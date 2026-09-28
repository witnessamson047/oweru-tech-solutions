<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\InvoiceSettlementService;
use App\Services\PesapalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Reconcile PesaPal payments with reality.
 *
 * Two failure modes this fixes:
 *
 * 1. Completed-but-unsettled payments: the money arrived but the browser
 *    callback never ran (customer closed the tab) and the IPN was missed
 *    (server down, PESAPAL_IPN_URL unset, network blip). Their invoice was
 *    never advanced and the automatic receipt never fired. This command
 *    settles them now.
 *
 * 2. Stale pending payments: PesaPal still shows PENDING hours later. We ask
 *    PesaPal for the final status and update the record — completed ones get
 *    settled in the same pass, failed/cancelled ones are closed out so they
 *    stop looking like "money on the way" in the admin.
 */
class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile
                            {--hours=1 : Re-check payments pending for at least this many hours}
                            {--dry-run : Only report what would change}';

    protected $description = 'Reconcile PesaPal payments: settle completed payments that were missed and resolve stale pending ones';

    // PesaPal keeps PENDING for a while, but past this many hours a payment is
    // worth re-asking about. Daily reminders start after this too.
    protected int $staleHours = 24;

    public function handle(InvoiceSettlementService $settlement, PesapalService $pesapal): int
    {
        $minAgeHours = max(0, (int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');

        $settledNow = $this->settleMissed($settlement, $dryRun);
        $resolved = $this->resolveStalePending($pesapal, $settlement, $minAgeHours, $dryRun);

        if ($settledNow === 0 && $resolved === 0) {
            $this->info('Nothing to reconcile — all payments match PesaPal.');

            return self::SUCCESS;
        }

        $this->info("Reconciled: {$settledNow} settled, {$resolved} resolved from PesaPal.");

        return self::SUCCESS;
    }

    /**
     * Mode 1: money arrived (status=completed) but was never applied to its
     * invoice. Cheap — no PesaPal calls needed.
     */
    protected function settleMissed(InvoiceSettlementService $settlement, bool $dryRun): int
    {
        $missed = Payment::query()
            ->where('status', 'completed')
            ->whereNotNull('invoice_id')
            ->whereNull('settled_at')
            ->with('invoice')
            ->get();

        if ($missed->isEmpty()) {
            return 0;
        }

        $this->line("Completed-but-unsettled payments: {$missed->count()}");

        if ($dryRun) {
            foreach ($missed as $payment) {
                $this->line("  would settle: {$payment->merchant_reference} → " . ($payment->invoice?->number ?? 'no invoice'));
            }

            return 0;
        }

        $count = 0;

        foreach ($missed as $payment) {
            if ($settlement->settle($payment)) {
                $count++;
                $this->info("  settled: {$payment->merchant_reference} → {$payment->invoice?->number}");
            }
        }

        return $count;
    }

    /**
     * Mode 2: pending payments older than --hours: ask PesaPal for the real
     * status. Completed → mark + settle (receipt fires). Failed/cancelled →
     * close out. Still pending → leave, but flag for staff after 24h.
     */
    protected function resolveStalePending(PesapalService $pesapal, InvoiceSettlementService $settlement, int $minAgeHours, bool $dryRun): int
    {
        $payments = Payment::query()
            ->where('status', 'pending')
            ->whereNotNull('order_tracking_id')
            ->where('created_at', '<=', now()->subHours($minAgeHours))
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        if ($payments->isEmpty()) {
            return 0;
        }

        $this->line("Stale pending payments to re-check with PesaPal: {$payments->count()}");

        $resolved = 0;

        foreach ($payments as $payment) {
            try {
                $status = $pesapal->getTransactionStatus($payment->order_tracking_id);
            } catch (\Throwable $e) {
                $this->warn("  PesaPal unreachable for {$payment->merchant_reference}: {$e->getMessage()}");

                continue;
            }

            $pesapalStatus = strtolower((string) ($status['status'] ?? ''));

            if ($pesapalStatus === '') {
                continue;
            }

            if ($dryRun) {
                $this->line("  {$payment->merchant_reference}: PesaPal says {$pesapalStatus}");

                continue;
            }

            if ($pesapalStatus === 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => $payment->paid_at ?? now(),
                    'pesapal_status' => $status['status'],
                    'payment_method' => $status['payment_method'] ?? null,
                ]);

                $this->advanceEnquiryStage($payment);
                $settlement->settle($payment->fresh());

                $this->info("  completed: {$payment->merchant_reference} → settled into invoice");
                $resolved++;
            } elseif (in_array($pesapalStatus, ['failed', 'invalid', 'canceled', 'cancelled'], true)) {
                $payment->update([
                    'status' => 'failed',
                    'pesapal_status' => $status['status'],
                    'payment_method' => $status['payment_method'] ?? null,
                ]);

                $this->line("  closed as {$pesapalStatus}: {$payment->merchant_reference}");
                $resolved++;
            } elseif ($this->staleHours > 0 && $payment->created_at->lt(now()->subHours($this->staleHours))) {
                // Genuinely stuck: log loudly so staff can check PesaPal's
                // dashboard manually. Status stays pending.
                $this->warn("  still pending after {$this->staleHours}h: {$payment->merchant_reference} ({$payment->description})");

                Log::warning('Payment pending for over 24h — manual check recommended', [
                    'payment' => $payment->merchant_reference,
                    'order_tracking_id' => $payment->order_tracking_id,
                    'amount' => $payment->amount,
                    'age_hours' => (int) $payment->created_at->diffInHours(now()),
                ]);
            }
        }

        return $resolved;
    }

    /**
     * Same enquiry-stage advance the callback performs, so late-completing
     * payments still move the pipeline (e.g. diagnostic_paid).
     */
    protected function advanceEnquiryStage(Payment $payment): void
    {
        // The controller's version is protected; replicate the small core here
        // to keep the command self-contained.
        if (! $payment->enquiry_id) {
            return;
        }

        $enquiry = \App\Models\Enquiry::find($payment->enquiry_id);

        if (! $enquiry) {
            return;
        }

        $newStage = match ($payment->paymentable_type) {
            \App\Models\Scan::class => 'diagnostic_paid',
            default => 'qualified',
        };

        $currentIndex = array_search($enquiry->stage, \App\Models\Enquiry::STAGES);
        $newIndex = array_search($newStage, \App\Models\Enquiry::STAGES);

        if ($currentIndex !== false && $newIndex !== false && $newIndex > $currentIndex) {
            $enquiry->update([
                'stage' => $newStage,
                'last_stage_changed_at' => now(),
            ]);
        }
    }
}
