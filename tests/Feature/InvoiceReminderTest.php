<?php

namespace Tests\Feature;

use App\Mail\DepositReminderMail;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceReminderTest extends TestCase
{
    private array $createdEnquiryIds = [];

    private array $createdInvoiceIds = [];

    protected function tearDown(): void
    {
        Notification::whereIn('enquiry_id', $this->createdEnquiryIds)->delete();
        Payment::whereIn('invoice_id', $this->createdInvoiceIds)->delete();
        Invoice::whereIn('id', $this->createdInvoiceIds)->delete();
        Enquiry::whereIn('id', $this->createdEnquiryIds)->delete();

        parent::tearDown();
    }

    private function makeEnquiry(): Enquiry
    {
        $suffix = uniqid();

        $enquiry = Enquiry::create([
            'name' => 'Reminder Customer ' . $suffix,
            'business_name' => 'Biz ' . $suffix,
            'email' => "reminder-{$suffix}@example.com",
            'phone' => '+255700111222',
            'problem_description' => 'Needs a website.',
            'budget_range' => 'Prefer not to say',
            'stage' => 'proposal_sent',
            'source' => 'direct',
        ]);

        $this->createdEnquiryIds[] = $enquiry->id;

        return $enquiry;
    }

    private function makeInvoice(array $overrides = []): Invoice
    {
        $enquiry = $overrides['enquiry'] ?? $this->makeEnquiry();
        unset($overrides['enquiry']);

        $invoice = Invoice::create(array_merge([
            'number' => 'OWU-INV-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'title' => 'Website Project',
            'total' => 1000000,
            'deposit_percent' => 50,
            'deposit_due' => 500000,
            'status' => Invoice::STATUS_ISSUED,
            'due_at' => now()->subDays(5),
        ], $overrides));

        $this->createdInvoiceIds[] = $invoice->id;

        return $invoice;
    }

    public function test_overdue_issued_invoice_is_selected_for_reminder(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();

        $service = app(\App\Services\InvoiceService::class);

        $this->assertTrue($service->overdueInvoices()->contains('id', $invoice->id));

        $sent = $service->sendOverdueReminders();
        $this->assertSame(1, $sent);

        $invoice = $invoice->fresh();
        $this->assertSame(1, $invoice->reminders_sent);
        $this->assertNotNull($invoice->last_reminder_sent_at);

        Mail::assertSent(DepositReminderMail::class, 1);

        // Staff were alerted too (one notification per admin recipient)
        $alerts = Notification::where('type', 'overdue_deposit')
            ->where('enquiry_id', $invoice->enquiry_id)
            ->get();

        $this->assertGreaterThan(0, $alerts->count(), 'Staff should be notified about the overdue deposit');
        $this->assertSame(1, $alerts->pluck('status')->unique()->count());
        $this->assertSame('sent', $alerts->first()->status);
        $this->assertStringContainsStringIgnoringCase('overdue', $alerts->first()->subject);
        $this->assertStringContainsString($invoice->number, $alerts->first()->message);
    }

    public function test_staff_alerts_fire_on_every_reminder_cadence(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();
        $service = app(\App\Services\InvoiceService::class);

        // First reminder
        $service->sendOverdueReminders();
        $firstCount = Notification::where('type', 'overdue_deposit')->count();
        $this->assertGreaterThan(0, $firstCount);

        // Backdate past the interval so a second reminder fires
        $invoice->update(['last_reminder_sent_at' => now()->subDays(4)]);
        $service->sendOverdueReminders();

        $this->assertGreaterThan($firstCount, Notification::where('type', 'overdue_deposit')->count());
        $this->assertSame(2, $invoice->fresh()->reminders_sent);
    }

    public function test_staff_alert_includes_phone_for_follow_up(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice();

        app(\App\Services\InvoiceService::class)->sendOverdueReminders();

        $alert = Notification::where('type', 'overdue_deposit')
            ->where('enquiry_id', $invoice->enquiry_id)
            ->first();

        $this->assertNotNull($alert);
        $this->assertStringContainsString($invoice->enquiry->phone, $alert->message);
        $this->assertStringContainsString('call', strtolower($alert->message));
        $this->assertStringContainsString('Action:', $alert->message);
    }

    public function test_not_yet_due_invoice_is_not_reminded(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice(['due_at' => now()->addDays(3)]);

        $this->assertFalse(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));
        $this->assertSame(0, app(\App\Services\InvoiceService::class)->sendOverdueReminders());

        Mail::assertNothingSent();
    }

    public function test_deposit_paid_invoice_is_not_reminded(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice(['status' => Invoice::STATUS_PART_PAID]);

        Payment::create([
            'paymentable_type' => Invoice::class,
            'paymentable_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'enquiry_id' => $invoice->enquiry_id,
            'kind' => Payment::KIND_DEPOSIT,
            'merchant_reference' => 'OWU-TEST-' . uniqid(),
            'amount' => 500000,
            'currency' => 'TZS',
            'description' => 'Deposit',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $this->assertFalse($invoice->fresh()->is_overdue);
        $this->assertFalse(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));

        Mail::assertNothingSent();
    }

    public function test_cancelled_invoice_is_not_reminded(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice(['status' => Invoice::STATUS_CANCELLED]);

        $this->assertFalse($invoice->fresh()->is_overdue);
        $this->assertFalse(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));

        Mail::assertNothingSent();
    }

    public function test_reminder_interval_prevents_repeat_emails(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice([
            'reminders_sent' => 1,
            'last_reminder_sent_at' => now()->subDays(1), // interval is 3 days
        ]);

        $this->assertFalse(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));
        $this->assertSame(0, app(\App\Services\InvoiceService::class)->sendOverdueReminders());

        Mail::assertNothingSent();
    }

    public function test_reminder_sent_again_after_interval_passes(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice([
            'reminders_sent' => 1,
            'last_reminder_sent_at' => now()->subDays(4), // interval is 3 days
        ]);

        $this->assertTrue(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));
        $this->assertSame(1, app(\App\Services\InvoiceService::class)->sendOverdueReminders());

        $this->assertSame(2, $invoice->fresh()->reminders_sent);
        Mail::assertSent(DepositReminderMail::class, 1);
    }

    public function test_max_reminders_cap_stops_emails(): void
    {
        Mail::fake();

        $invoice = $this->makeInvoice([
            'reminders_sent' => 4, // max_reminders default = 4
            'last_reminder_sent_at' => now()->subDays(10),
        ]);

        $this->assertFalse(app(\App\Services\InvoiceService::class)->overdueInvoices()->contains('id', $invoice->id));
        $this->assertSame(0, app(\App\Services\InvoiceService::class)->sendOverdueReminders());

        Mail::assertNothingSent();
    }

    public function test_reminder_command_reports_sent_count(): void
    {
        Mail::fake();

        $this->makeInvoice();

        $this->artisan('invoices:send-overdue-reminders')
            ->expectsOutputToContain('reminders sent')
            ->assertSuccessful();

        Mail::assertSent(DepositReminderMail::class, 1);
    }

    public function test_days_overdue_is_calculated(): void
    {
        $invoice = $this->makeInvoice(['due_at' => now()->subDays(5)]);

        $this->assertSame(5, $invoice->days_overdue);
        $this->assertTrue($invoice->is_overdue);
    }
}
