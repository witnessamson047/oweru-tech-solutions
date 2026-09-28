<?php

namespace App\Observers;

use App\Models\Enquiry;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;

class EnquiryObserver
{
    public function __construct(
        protected InvoiceService $invoices,
    ) {}

    /**
     * When an enquiry reaches 'proposal_sent', automatically create and issue
     * an invoice with the configured deposit (default 50% upfront).
     */
    public function updated(Enquiry $enquiry): void
    {
        if (! $enquiry->wasChanged('stage')) {
            return;
        }

        if ($enquiry->stage !== 'proposal_sent') {
            return;
        }

        // Guard against double-firing when the update itself creates the invoice.
        if ($enquiry->invoices()->notCancelled()->exists()) {
            return;
        }

        try {
            $invoice = $this->invoices->createForEnquiry($enquiry);
            $this->invoices->issue($invoice);

            Log::info('Auto-invoice issued at proposal_sent', [
                'enquiry_id' => $enquiry->id,
                'invoice' => $invoice->number,
                'deposit' => $invoice->deposit_due,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Auto-invoice failed at proposal_sent', [
                'enquiry_id' => $enquiry->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
