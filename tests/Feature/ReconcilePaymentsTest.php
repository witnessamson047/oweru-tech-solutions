<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceSettlementService;
use App\Services\InvoiceService;
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReconcilePaymentsTest extends TestCase
{
    private array $createdEnquiryIds = [];

    private array $createdInvoiceIds = [];

    private array $createdPaymentIds = [];

    protected function tearDown(): void
    {
        Payment::whereIn('id', $this->createdPaymentIds)->delete();
        Invoice::whereIn('id', $this->createdInvoiceIds)->delete();
        Enquiry::whereIn('id', $this->createdEnquiryIds)->delete();

        parent::tearDown();
    }

    private function makeInvoice(array $overrides = []): Invoice
    {
        $suffix = uniqid();

        $enquiry = Enquiry::create([
            'name' => 'Reconcile Customer ' . $suffix,
            'business_name' => 'Biz ' . $suffix,
            'email' => "reconcile-{$suffix}@example.com",
            'phone' => '+255700111222',
            'problem_description' => 'Needs a website.',
            'budget_range' => 'Prefer not to say',
            'stage' => 'qualified',
            'source' => 'direct',
        ]);
        $this->createdEnquiryIds[] = $enquiry->id;

        $invoice = Invoice::create(array_merge([
            'number' => 'OWU-INV-TEST-' . $suffix,
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
        ], $overrides));
        $this->createdInvoiceIds[] = $invoice->id;

        return $invoice;
    }

    private function makePayment(Invoice $invoice, array $overrides = []): Payment
    {
        $payment = Payment::create(array_merge([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $invoice->enquiry_id,
            'kind' => Payment::KIND_FULL,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 1000000,
            'currency' => 'TZS',
            'description' => 'Full settlement',
            'status' => 'completed',
            'paid_at' => now(),
        ], $overrides));
        $this->createdPaymentIds[] = $payment->id;

        // created_at is not mass-assignable — set it directly when a test needs
        // the payment to look older than it is.
        if (isset($overrides['_created_ago_hours'])) {
            $payment->created_at = now()->subHours($overrides['_created_ago_hours']);
            $payment->save();
        }

        return $payment;
    }

    public function test_reconcile_settles_completed_payment_that_never_reached_the_invoice(): void
    {
        Mail::fake();

        // Deposit already settled; balance payment completed but callback/IPN never landed
        $invoice = $this->makeInvoice(['status' => Invoice::STATUS_PART_PAID]);

        $this->makePayment($invoice, [
            'kind' => Payment::KIND_DEPOSIT,
            'amount' => 500000,
            'settled_at' => now(),
        ]);

        $missedBalance = $this->makePayment($invoice, [
            'kind' => Payment::KIND_BALANCE,
            'amount' => 500000,
            'settled_at' => null,
        ]);

        $this->artisan('payments:reconcile')->assertSuccessful();

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertTrue($invoice->is_fully_paid);
        $this->assertNotNull($invoice->receipt_generated_at, 'Reconciliation must trigger the automatic receipt');
        $this->assertNotNull($missedBalance->fresh()->settled_at, 'Payment must be marked settled');

        @unlink($invoice->receipt_path);
        Mail::assertSent(ReceiptMail::class);
    }

    public function test_reconcile_resolves_stale_pending_payment_as_completed(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();

        $payment = $this->makePayment($invoice, [
            'status' => 'pending',
            'paid_at' => null,
            'order_tracking_id' => 'TRACK-' . uniqid(),
            '_created_ago_hours' => 3,
        ]);

        // Scope fakes to PesaPal endpoints — a catch-all '*' would also
        // intercept the token request and break PesapalService auth.
        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token'], 200),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'status' => 'COMPLETED',
                'payment_method' => 'MPESA',
            ], 200),
        ]);

        $this->artisan('payments:reconcile', ['--hours' => 1])->assertSuccessful();

        $payment = $payment->fresh();
        $this->assertSame('completed', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->settled_at);

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($invoice->receipt_generated_at);
        @unlink($invoice->receipt_path);
    }

    public function test_reconcile_closes_stale_pending_payment_reported_failed(): void
    {
        $invoice = $this->makeInvoice();

        $payment = $this->makePayment($invoice, [
            'status' => 'pending',
            'paid_at' => null,
            'order_tracking_id' => 'TRACK-' . uniqid(),
            '_created_ago_hours' => 3,
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token'], 200),
            '*/api/Transactions/GetTransactionStatus*' => Http::response(['status' => 'FAILED'], 200),
        ]);

        $this->artisan('payments:reconcile', ['--hours' => 1])->assertSuccessful();

        $this->assertSame('failed', $payment->fresh()->status);

        $invoice = $invoice->fresh();
        $this->assertNotSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertNull($invoice->receipt_generated_at);
    }

    public function test_fresh_pending_payment_is_not_rechecked(): void
    {
        $invoice = $this->makeInvoice();

        $payment = $this->makePayment($invoice, [
            'status' => 'pending',
            'paid_at' => null,
            'order_tracking_id' => 'TRACK-' . uniqid(),
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token'], 200),
            '*/api/Transactions/GetTransactionStatus*' => Http::response(['status' => 'FAILED'], 200),
        ]);

        $this->artisan('payments:reconcile', ['--hours' => 1])->assertSuccessful();

        // Created seconds ago → outside the --hours=1 window → untouched
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame(Invoice::STATUS_ISSUED, $invoice->fresh()->status);
    }

    public function test_settle_is_idempotent_when_called_twice(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();
        $payment = $this->makePayment($invoice);

        $service = app(InvoiceSettlementService::class);

        $this->assertTrue($service->settle($payment));
        $this->assertFalse($service->settle($payment), 'Second settle of the same payment must be a no-op');

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertNotNull($invoice->receipt_generated_at);

        @unlink($invoice->receipt_path);
    }

    public function test_apply_payment_legacy_entry_point_still_works(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();
        $payment = $this->makePayment($invoice, [
            'kind' => Payment::KIND_DEPOSIT,
            'amount' => 500000,
        ]);

        app(InvoiceService::class)->applyPayment($payment);

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PART_PAID, $invoice->status);
        $this->assertNotNull($payment->fresh()->settled_at);
    }
}
