<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    private array $createdEnquiryIds = [];

    private array $createdInvoiceIds = [];

    protected function tearDown(): void
    {
        // Clean in FK-safe order
        Payment::whereIn('invoice_id', $this->createdInvoiceIds)->delete();
        Invoice::whereIn('id', $this->createdInvoiceIds)->delete();
        Enquiry::whereIn('id', $this->createdEnquiryIds)->delete();

        parent::tearDown();
    }

    private function makeEnquiry(array $overrides = []): Enquiry
    {
        $suffix = uniqid();

        $enquiry = Enquiry::create(array_merge([
            'name' => 'Test Customer ' . $suffix,
            'business_name' => 'Biz ' . $suffix,
            'email' => "customer-{$suffix}@example.com",
            'phone' => '+255700111222',
            'problem_description' => 'Needs a website.',
            'budget_range' => 'Prefer not to say',
            'stage' => 'qualified',
            'source' => 'direct',
        ], $overrides));

        $this->createdEnquiryIds[] = $enquiry->id;

        return $enquiry;
    }

    public function test_invoice_auto_created_when_enquiry_reaches_proposal_sent(): void
    {
        Mail::fake();

        $enquiry = $this->makeEnquiry();

        $enquiry->update(['stage' => 'proposal_sent']);

        $invoice = Invoice::where('enquiry_id', $enquiry->id)->first();
        $this->createdInvoiceIds[] = $invoice->id ?? 0;

        $this->assertNotNull($invoice, 'Invoice should be auto-created at proposal_sent');
        $this->assertSame(Invoice::STATUS_ISSUED, $invoice->status);
        $this->assertSame(50, $invoice->deposit_percent);
        $this->assertNotNull($invoice->due_at);
        $this->assertStringStartsWith('OWU-INV-' . now()->format('Y') . '-', $invoice->number);
    }

    public function test_auto_invoice_is_not_duplicated(): void
    {
        Mail::fake();

        $enquiry = $this->makeEnquiry();
        $enquiry->update(['stage' => 'proposal_sent']);
        $enquiry->update(['stage' => 'won']); // stage changes again
        $enquiry->update(['stage' => 'proposal_sent']); // back to proposal_sent

        $this->assertSame(1, Invoice::where('enquiry_id', $enquiry->id)->count());
    }

    public function test_deposit_math_and_next_payment(): void
    {
        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        $this->assertSame(500000.0, $invoice->deposit_amount);
        $this->assertSame(500000.0, $invoice->balance_amount);
        $this->assertSame(500000.0, $invoice->next_payment_amount);
        $this->assertSame(Payment::KIND_DEPOSIT, $invoice->getNextPaymentKind());
        $this->assertFalse($invoice->is_deposit_paid);
    }

    public function test_applying_deposit_moves_invoice_to_part_paid(): void
    {
        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        $payment = Payment::create([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $enquiry->id,
            'kind' => Payment::KIND_DEPOSIT,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 500000,
            'currency' => 'TZS',
            'description' => 'Deposit',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        app(\App\Services\InvoiceService::class)->applyPayment($payment);

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PART_PAID, $invoice->status);
        $this->assertTrue($invoice->is_deposit_paid);
        $this->assertFalse($invoice->is_fully_paid);
    }

    public function test_full_settlement_marks_paid_and_generates_receipt(): void
    {
        Mail::fake();

        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_PART_PAID,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        // Deposit already paid
        Payment::create([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $enquiry->id,
            'kind' => Payment::KIND_DEPOSIT,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 500000,
            'currency' => 'TZS',
            'description' => 'Deposit',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Balance payment completes the invoice
        $balance = Payment::create([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $enquiry->id,
            'kind' => Payment::KIND_BALANCE,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 500000,
            'currency' => 'TZS',
            'description' => 'Balance',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        app(\App\Services\InvoiceService::class)->applyPayment($balance);

        $invoice = $invoice->fresh();
        $this->assertSame(Invoice::STATUS_PAID, $invoice->status);
        $this->assertTrue($invoice->is_fully_paid);
        $this->assertNotNull($invoice->receipt_generated_at, 'Receipt should be generated automatically');
        $this->assertFileExists($invoice->receipt_path);

        @unlink($invoice->receipt_path);

        Mail::assertSent(\App\Mail\ReceiptMail::class);
    }

    public function test_admin_can_create_and_issue_invoice_manually(): void
    {
        Mail::fake();

        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $enquiry = $this->makeEnquiry();

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'enquiry_id' => $enquiry->id,
            'total' => 800000,
            'deposit_percent' => 50,
            'issue_now' => '1',
        ]);

        $response->assertRedirect();

        $invoice = Invoice::where('enquiry_id', $enquiry->id)->first();
        $this->createdInvoiceIds[] = $invoice->id ?? 0;

        $this->assertNotNull($invoice);
        $this->assertSame(800000.0, (float) $invoice->total);
        $this->assertSame(400000.0, (float) $invoice->deposit_due);
        $this->assertSame(Invoice::STATUS_ISSUED, $invoice->status);

        Mail::assertSent(\App\Mail\InvoiceMail::class);
    }

    public function test_invoice_checkout_page_renders_with_deposit_amount(): void
    {
        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        $response = $this->get(route('payment.checkout', ['type' => 'invoice-deposit', 'id' => $invoice->id]));

        $response->assertStatus(200);
        $response->assertSee('Deposit (50%)');
        $response->assertSee('1,000,000');
    }

    public function test_paid_invoice_checkout_returns_404(): void
    {
        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000,
            'deposit_percent' => 50,
            'deposit_due' => 500,
            'status' => Invoice::STATUS_PAID,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        Payment::create([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $enquiry->id,
            'kind' => Payment::KIND_FULL,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 1000,
            'currency' => 'TZS',
            'description' => 'Full',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $this->get(route('payment.checkout', ['type' => 'invoice-deposit', 'id' => $invoice->id]))
            ->assertStatus(404);
    }

    public function test_admin_invoice_pages_render(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $enquiry = $this->makeEnquiry();
        $invoice = Invoice::create([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
        ]);
        $this->createdInvoiceIds[] = $invoice->id;

        $this->actingAs($admin)->get(route('admin.invoices.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.invoices.create'))->assertStatus(200);
    }
}
