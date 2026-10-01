<?php

namespace App\Services;

use App\Mail\DepositReminderMail;
use App\Mail\InvoiceMail;
use App\Mail\ReceiptMail;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\LocalPath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceService
{
    /**
     * Create (or return the existing) invoice for an enquiry.
     */
    public function createForEnquiry(Enquiry $enquiry, ?float $total = null, ?string $title = null, int $depositPercent = 50): Invoice
    {
        if ($existing = $enquiry->invoices()->notCancelled()->first()) {
            return $existing;
        }

        $total = $total ?: $this->estimateTotal($enquiry);
        $depositPercent = max(0, min(100, $depositPercent));
        $depositDue = round($total * $depositPercent / 100, 2);

        $invoice = Invoice::create([
            'number' => $this->nextNumber(),
            'enquiry_id' => $enquiry->id,
            'title' => $title ?: $this->defaultTitle($enquiry),
            'description' => "Service agreement for {$enquiry->business_name}. "
                . "{$depositPercent}% deposit (TZS " . number_format($depositDue) . ") is required before work begins; "
                . 'the balance is payable on completion.',
            'total' => $total,
            'currency' => 'TZS',
            'deposit_percent' => $depositPercent,
            'deposit_due' => $depositDue,
            'status' => Invoice::STATUS_DRAFT,
            'meta' => [
                'created_via' => 'system',
                'package' => $enquiry->package_name,
                'scan_score' => $enquiry->scan?->score,
            ],
        ]);

        $enquiry->loadMissing('invoices');

        return $invoice;
    }

    /**
     * Mark a draft invoice as issued, set the deposit due date, and email the customer.
     */
    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return $invoice;
        }

        $invoice->update([
            'status' => Invoice::STATUS_ISSUED,
            'issued_at' => now(),
            'due_at' => now()->addDays((int) config('owers.invoice.deposit_due_days', 7)),
        ]);

        $this->emailInvoice($invoice);

        return $invoice->fresh();
    }

    /**
     * Apply a completed payment to the invoice, advance its status, and
     * generate the automatic receipt once everything is settled.
     *
     * Kept for compatibility — the race-safe path is
     * InvoiceSettlementService::settle(), which callback, IPN and
     * payments:reconcile all use.
     */
    public function applyPayment(Payment $payment): void
    {
        app(InvoiceSettlementService::class)->settle($payment);
    }

    /**
     * Generate the receipt PDF automatically and email it to the customer.
     * Idempotent — a receipt is only generated once per invoice.
     */
    public function generateReceipt(Invoice $invoice, bool $force = false, bool $notify = true): ?string
    {
        if ($invoice->receipt_generated_at && $invoice->receipt_path && ! $force) {
            return $invoice->receipt_path;
        }

        try {
            $invoice->load(['completedPayments', 'enquiry']);

            $dir = LocalPath::receiptsDir();
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $fileName = "oweru-receipt-{$invoice->number}.pdf";
            $filePath = "{$dir}/{$fileName}";

            Pdf::loadView('pdf.receipt', ['invoice' => $invoice])->save($filePath);

            $invoice->update([
                'receipt_path' => $filePath,
                'receipt_generated_at' => now(),
            ]);

            if ($notify) {
                Mail::to($this->customerEmail($invoice))->send(new ReceiptMail($invoice));
            }

            Log::info('Receipt generated', ['invoice' => $invoice->number, 'path' => $filePath, 'notified' => $notify]);

            return $filePath;
        } catch (\Throwable $e) {
            Log::warning('Receipt generation failed', [
                'invoice' => $invoice->number,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Email the invoice PDF to the customer (deposit instructions included).
     */
    public function emailInvoice(Invoice $invoice): void
    {
        try {
            Mail::to($this->customerEmail($invoice))->send(new InvoiceMail($invoice));
        } catch (\Throwable $e) {
            Log::warning('Invoice email failed', [
                'invoice' => $invoice->number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invoices whose deposit is overdue and that are due a reminder right now:
     * issued/part-paid, deposit unpaid, due date passed, last reminder older
     * than the interval, and fewer than the max reminders already sent.
     */
    public function overdueInvoices(): \Illuminate\Support\Collection
    {
        $interval = (int) config('owers.invoice.reminder_interval_days', 3);
        $max = (int) config('owers.invoice.max_reminders', 4);

        if ($max <= 0) {
            return collect();
        }

        return Invoice::query()
            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PART_PAID])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->endOfDay())
            ->get()
            ->filter(fn (Invoice $invoice) =>
                $invoice->is_overdue
                && $invoice->reminders_sent < $max
                && (
                    $invoice->last_reminder_sent_at === null
                    || $invoice->last_reminder_sent_at->copy()->addDays($interval)->isPast()
                )
            )
            ->values();
    }

    /**
     * Email one overdue-deposit reminder per due invoice.
     * Returns the number of reminders sent.
     */
    public function sendOverdueReminders(): int
    {
        $sent = 0;

        foreach ($this->overdueInvoices() as $invoice) {
            try {
                Mail::to($this->customerEmail($invoice))->send(new DepositReminderMail($invoice));

                // Tell staff to follow up by phone (same cadence as the
                // customer reminder; failures must not block the loop).
                try {
                    app(NotificationService::class)->overdueDeposit($invoice);
                } catch (\Throwable $e) {
                    Log::warning('Staff overdue alert failed', [
                        'invoice' => $invoice->number,
                        'error' => $e->getMessage(),
                    ]);
                }

                $invoice->update([
                    'reminders_sent' => $invoice->reminders_sent + 1,
                    'last_reminder_sent_at' => now(),
                ]);

                $sent++;

                Log::info('Overdue deposit reminder sent', [
                    'invoice' => $invoice->number,
                    'days_overdue' => $invoice->days_overdue,
                    'reminder_number' => $invoice->reminders_sent,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Overdue deposit reminder failed', [
                    'invoice' => $invoice->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Build the invoice PDF.
     */
    public function pdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['completedPayments', 'enquiry']);

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
    }

    /**
     * Next sequential invoice number: OWU-INV-2026-0001
     */
    protected function nextNumber(): string
    {
        return DB::transaction(function () {
            $year = now()->format('Y');
            $prefix = config('owers.invoice.number_prefix', 'OWU-INV') . "-{$year}-";

            $last = Invoice::where('number', 'like', "{$prefix}%")
                ->orderByDesc('number')
                ->lockForUpdate()
                ->first();

            $seq = $last ? ((int) substr($last->number, strlen($prefix)) + 1) : 1;

            return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Estimate a total from the enquiry's package (fallback: flat quote).
     */
    protected function estimateTotal(Enquiry $enquiry): float
    {
        if ($enquiry->package?->price_tzs) {
            return (float) $enquiry->package->price_tzs;
        }

        // Sensible default project quote when no package is attached.
        return 1500000.0; // TZS 1.5M
    }

    protected function defaultTitle(Enquiry $enquiry): string
    {
        return $enquiry->package_name
            ?: ($enquiry->package?->name ?: 'Website Services Agreement');
    }

    protected function customerEmail(Invoice $invoice): ?string
    {
        return $invoice->enquiry?->email;
    }
}
