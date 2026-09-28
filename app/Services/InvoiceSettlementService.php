<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Applies completed payments to invoices and generates receipts — exactly once.
 *
 * PesaPal notifies us through two independent channels (browser callback and
 * server IPN), and payments can also be settled retroactively by the
 * payments:reconcile command. All three paths funnel through here, so the
 * invoice status transitions and the automatic receipt happen once, not twice,
 * even when two paths race each other.
 */
class InvoiceSettlementService
{
    public function __construct(
        protected InvoiceService $invoices,
    ) {}

    /**
     * Apply one completed payment to its invoice (if any).
     *
     * Returns true when this call actually advanced the invoice (i.e. the
     * caller was the one to settle it), false when the payment was already
     * applied or the invoice is not settleable.
     */
    public function settle(Payment $payment): bool
    {
        $invoice = $payment->invoice;

        if (! $invoice || $invoice->status === Invoice::STATUS_CANCELLED) {
            return false;
        }

        // Only ever settle a payment that PesaPal (or an admin) marked completed,
        // and never settle the same payment twice.
        if ($payment->status !== 'completed' || $payment->settled_at !== null) {
            return false;
        }

        // Cross-process lock: IPN + callback can arrive within milliseconds
        // of each other; without this both would run the transition below.
        $lock = Cache::lock("invoice-settle:{$invoice->id}", 30);

        try {
            // Block up to 10s for the other path to finish, then give up —
            // reconcile will pick up anything missed.
            $lock->block(10);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException) {
            Log::warning('Invoice settlement lock timed out — payment left for reconciliation', [
                'payment' => $payment->merchant_reference,
                'invoice' => $invoice->number,
            ]);

            return false;
        }

        try {
            // Re-read inside the lock: the other path may have settled it already.
            $payment->refresh();
            $invoice->refresh();

            if ($payment->settled_at !== null || $invoice->status === Invoice::STATUS_CANCELLED) {
                return false;
            }

            $invoice->load('completedPayments');

            if ($invoice->is_fully_paid) {
                if ($invoice->status !== Invoice::STATUS_PAID) {
                    $invoice->update(['status' => Invoice::STATUS_PAID]);
                }

                // Only the run that flips the invoice to paid generates the
                // receipt — idempotent per invoice anyway, but this keeps
                // logs honest about who did it.
                $this->invoices->generateReceipt($invoice);
            } elseif ($invoice->amount_paid > 0) {
                $invoice->update(['status' => Invoice::STATUS_PART_PAID]);
            }

            // Mark settled LAST: if anything above throws, reconcile retries it.
            $payment->update(['settled_at' => now()]);

            Log::info('Payment settled into invoice', [
                'payment' => $payment->merchant_reference,
                'invoice' => $invoice->number,
                'fully_paid' => $invoice->is_fully_paid,
                'receipt_generated' => (bool) $invoice->fresh()->receipt_generated_at,
            ]);

            return true;
        } finally {
            $lock->release();
        }
    }

    /**
     * Settle every completed-but-unsettled payment.
     * Used by payments:reconcile (scheduled) to catch anything the callback
     * or IPN path missed.
     *
     * @return int number of payments settled now
     */
    public function settleAllPending(): int
    {
        $settled = 0;

        Payment::query()
            ->where('status', 'completed')
            ->whereNotNull('invoice_id')
            ->whereNull('settled_at')
            ->with('invoice')
            ->chunkById(100, function ($payments) use (&$settled) {
                foreach ($payments as $payment) {
                    if ($this->settle($payment)) {
                        $settled++;
                    }
                }
            });

        return $settled;
    }
}
