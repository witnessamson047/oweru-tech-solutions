<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ServicePackage;
use App\Models\CarePlan;
use App\Models\Scan;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Services\PesapalService;
use App\Services\InvoiceSettlementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Payment types:
 *  package / care-plan / scan  — direct purchases
 *  invoice-deposit             — the 50% (configurable) deposit on an invoice
 *  invoice-full                — pay the entire remaining invoice balance now
 */

class PaymentController extends Controller
{
    protected PesapalService $pesapal;

    public function __construct(PesapalService $pesapal)
    {
        $this->pesapal = $pesapal;
    }

    /**
     * Show checkout page for a payable item.
     */
    public function checkout(string $type, int $id)
    {
        $isInvoice = str_starts_with($type, 'invoice-');

        $payable = match (true) {
            $type === 'package' => ServicePackage::findOrFail($id),
            $type === 'care-plan' => CarePlan::findOrFail($id),
            $type === 'scan' => Scan::findOrFail($id),
            $isInvoice => Invoice::notCancelled()->findOrFail($id),
            default => abort(404),
        };

        if ($isInvoice) {
            /** @var Invoice $invoice */
            $invoice = $payable;
            abort_if($invoice->is_fully_paid, 404, 'This invoice is already paid.');

            $amount = (float) ($type === 'invoice-full' ? $invoice->balance_due : $invoice->next_payment_amount);
            $label = $type === 'invoice-full'
                ? "Full payment — {$invoice->title}"
                : "Deposit ({$invoice->deposit_percent}%) — {$invoice->title}";
            $description = "Invoice {$invoice->number} — {$label}";
        } else {
            $description = match ($type) {
                'package' => "Payment for {$payable->name} package",
                'care-plan' => "Subscription for {$payable->name} care plan",
                'scan' => "PDF Report for scan #{$payable->id} ({$payable->url})",
                default => 'Payment',
            };
            $amount = match ($type) {
                'package' => $payable->price_tzs,
                'care-plan' => $payable->price_tzs,
                'scan' => 10000, // Default scan report price TZS 10,000
                default => 0,
            };
            $label = $payable->name ?? 'Payment';
        }

        return view('payment.checkout', compact('type', 'payable', 'description', 'amount', 'label'));
    }

    /**
     * Initiate payment and redirect to PesaPal.
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:package,care-plan,scan,invoice-deposit,invoice-full',
            'id' => 'required|integer',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'name' => 'nullable|string',
        ]);

        $isInvoice = str_starts_with($validated['type'], 'invoice-');

        $payable = match (true) {
            $validated['type'] === 'package' => ServicePackage::findOrFail($validated['id']),
            $validated['type'] === 'care-plan' => CarePlan::findOrFail($validated['id']),
            $validated['type'] === 'scan' => Scan::findOrFail($validated['id']),
            $isInvoice => Invoice::notCancelled()->findOrFail($validated['id']),
            default => abort(404),
        };

        $invoiceId = null;
        $kind = Payment::KIND_DIRECT;

        if ($isInvoice) {
            /** @var Invoice $invoice */
            $invoice = $payable;
            abort_if($invoice->is_fully_paid, 404, 'This invoice is already paid.');

            $invoiceId = $invoice->id;
            $kind = $validated['type'] === 'invoice-full' ? Payment::KIND_FULL : Payment::KIND_DEPOSIT;
            $amount = (float) ($validated['type'] === 'invoice-full' ? $invoice->balance_due : $invoice->next_payment_amount);
            $description = "Invoice {$invoice->number} — "
                . ($validated['type'] === 'invoice-full' ? 'full payment' : "deposit ({$invoice->deposit_percent}%)");
            $enquiryId = $invoice->enquiry_id;
        } else {
            $amount = match ($validated['type']) {
                'package' => $payable->price_tzs,
                'care-plan' => $payable->price_tzs,
                'scan' => 10000,
                default => 0,
            };
            $description = match ($validated['type']) {
                'package' => "Payment for {$payable->name} package",
                'care-plan' => "Subscription for {$payable->name} care plan",
                'scan' => "PDF Report for scan #{$payable->id}",
                default => 'Payment',
            };
            $enquiryId = null;
        }

        // Create a pending payment record
        $merchantRef = 'OWU-' . strtoupper(Str::random(10)) . '-' . time();

        $payment = Payment::create([
            'paymentable_type' => $isInvoice ? Invoice::class : match ($validated['type']) {
                'package' => ServicePackage::class,
                'care-plan' => CarePlan::class,
                'scan' => Scan::class,
            },
            'paymentable_id' => $validated['id'],
            'enquiry_id' => $enquiryId,
            'invoice_id' => $invoiceId,
            'kind' => $kind,
            'merchant_reference' => $merchantRef,
            'amount' => $amount,
            'currency' => 'TZS',
            'description' => $description,
            'status' => 'pending',
            'metadata' => [
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'name' => $validated['name'] ?? null,
            ],
        ]);

        // Register IPN URL once and cache the IPN ID. Never cache a null: if
        // registration fails this attempt, the next payment retries it — a
        // cached null would silence PesaPal notifications for a whole day and
        // receipts would then only fire via the browser callback.
        $ipnId = $this->cachedIpnId();

        // Build the order request
        $orderData = [
            'id' => $merchantRef,
            'currency' => 'TZS',
            'amount' => $amount,
            'description' => $description,
            'callback_url' => route('payment.callback', ['ref' => $merchantRef]),
            'redirect_mode' => 'TOP_WINDOW',
            'notification_id' => $ipnId,
            'billing_address' => [
                'email_address' => $validated['email'],
                'phone_number' => $validated['phone'] ?? '',
                'country_code' => 'TZ',
                'first_name' => $validated['name'] ?? '',
                'last_name' => '',
            ],
        ];

        try {
            $result = $this->pesapal->submitOrder($orderData);

            // Update payment with PesaPal's order tracking ID
            $payment->update([
                'order_tracking_id' => $result['order_tracking_id'] ?? null,
            ]);

            // Redirect to PesaPal payment page
            return redirect($result['redirect_url']);
        } catch (\Exception $e) {
            return redirect()->route('payment.status', ['ref' => $merchantRef])
                ->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Callback URL — customer is redirected here after payment.
     */
    public function callback(Request $request)
    {
        $orderTrackingId = $request->query('OrderTrackingId');
        $merchantRef = $request->query('OrderMerchantReference');

        if (!$orderTrackingId || !$merchantRef) {
            return redirect()->route('payment.status', ['ref' => $merchantRef ?? 'unknown'])
                ->with('error', 'Invalid payment response.');
        }

        // Verify the transaction status
        $status = $this->pesapal->getTransactionStatus($orderTrackingId);

        $payment = Payment::where('merchant_reference', $merchantRef)->first();

        if ($payment) {
            // PENDING at callback time keeps the record pending (never failed) —
            // see applyStatus(); payments:reconcile resolves it later.
            $this->applyStatus($payment, $status);

            // Auto-advance enquiry stage if linked, then apply to the invoice
            // (status transitions + automatic receipt when fully paid).
            if ($payment->status === 'completed') {
                $this->advanceEnquiryStage($payment);
                app(InvoiceSettlementService::class)->settle($payment);
            }
        }

        return redirect()->route('payment.status', ['ref' => $merchantRef]);
    }

    /**
     * Cached PesaPal IPN id — null-safe: a failed registration is not cached,
     * so the next checkout retries instead of staying silent for 24h.
     */
    protected function cachedIpnId(): ?string
    {
        $cached = cache()->get('pesapal_ipn_id');

        if ($cached !== null) {
            return $cached;
        }

        $ipnUrl = config('services.pesapal.ipn_url');
        if (! $ipnUrl) {
            return null;
        }

        try {
            $result = $this->pesapal->registerIpn($ipnUrl, 'GET');
        } catch (\Throwable $e) {
            Log::warning('PesaPal IPN registration failed (will retry on next checkout)', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $ipnId = $result['ipn_id'] ?? null;

        if ($ipnId) {
            cache()->put('pesapal_ipn_id', $ipnId, 86400);
        }

        return $ipnId;
    }

    /**
     * Mark a payment completed/failed from a PesaPal status payload.
     *
     * CRITICAL: PesaPal is still processing many payments when the customer's
     * browser hits our callback — reporting PENDING there. Marking that
     * "failed" killed the payment record and the receipt never fired. PENDING
     * therefore keeps the record pending; only an explicit FAILED/ cancelled
     * status marks failure. payments:reconcile resolves pending payments.
     */
    protected function applyStatus(Payment $payment, array $status): void
    {
        $pesapalStatus = strtolower((string) ($status['status'] ?? ''));

        $update = [
            'pesapal_status' => $status['status'] ?? null,
            'payment_method' => $status['payment_method'] ?? null,
        ];

        if ($pesapalStatus === 'completed') {
            $update['status'] = 'completed';
            $update['paid_at'] = $payment->paid_at ?? now();
        } elseif (in_array($pesapalStatus, ['failed', 'invalid', 'canceled', 'cancelled'], true)) {
            // PesaPal v3 statuses: FAILED, CANCELED, INVALID (also catches
            // user-cancelled flows so records don't linger as pending forever).
            $update['status'] = 'failed';
        }
        // PENDING / IN_PROGRESS / anything unknown: leave status untouched.

        $payment->update($update);
    }

    /**
     * IPN notification — PesaPal sends this to our server.
     */
    public function ipn(Request $request)
    {
        $orderTrackingId = $request->query('OrderTrackingId') ?? $request->input('OrderTrackingId');
        $merchantRef = $request->query('OrderMerchantReference') ?? $request->input('OrderMerchantReference');

        if (!$orderTrackingId || !$merchantRef) {
            return response()->json(['message' => 'Missing parameters'], 400);
        }

        // Verify the transaction
        $status = $this->pesapal->getTransactionStatus($orderTrackingId);

        $payment = Payment::where('merchant_reference', $merchantRef)->first();

        if ($payment && strtolower($status['status'] ?? '') === 'completed') {
            $this->applyStatus($payment, $status);

            // Auto-advance enquiry stage, then apply to the invoice
            // (status transitions + automatic receipt when fully paid).
            $this->advanceEnquiryStage($payment);
            app(InvoiceSettlementService::class)->settle($payment);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Advance the enquiry stage based on payment type.
     * - Package/care-plan payment → 'qualified'
     * - Scan report payment → 'diagnostic_paid'
     */
    protected function advanceEnquiryStage(Payment $payment): void
    {
        // Try to find enquiry from payment, or from the linked scan/website
        $enquiry = null;

        if ($payment->enquiry_id) {
            $enquiry = Enquiry::find($payment->enquiry_id);
        }

        // For scan payments, also check the scan's enquiry
        if (!$enquiry && $payment->paymentable_type === Scan::class) {
            $scan = Scan::find($payment->paymentable_id);
            if ($scan) {
                $enquiry = $scan->enquiry ?? $scan->website?->enquiry;
                if ($enquiry) {
                    $payment->update(['enquiry_id' => $enquiry->id]);
                }
            }
        }

        if (!$enquiry) {
            return;
        }

        $newStage = match ($payment->paymentable_type) {
            Scan::class => 'diagnostic_paid',
            default => 'qualified',
        };

        // Only advance, never move backwards
        $currentStageIndex = array_search($enquiry->stage, Enquiry::STAGES);
        $newStageIndex = array_search($newStage, Enquiry::STAGES);

        if ($currentStageIndex !== false && $newStageIndex !== false && $newStageIndex > $currentStageIndex) {
            $enquiry->update([
                'stage' => $newStage,
                'last_stage_changed_at' => now(),
            ]);
        }
    }

    /**
     * Show payment status page.
     */
    public function status(string $ref)
    {
        $payment = Payment::where('merchant_reference', $ref)->firstOrFail();

        return view('payment.status', compact('payment'));
    }

    /**
     * Public receipt download, guarded by a signed-style token so only the
     * customer with the email link (or an admin) can fetch the PDF.
     */
    public function downloadReceipt(Invoice $invoice, string $token)
    {
        abort_unless(hash_equals($invoice->receipt_token, $token), 403);
        abort_unless($invoice->receipt_path && file_exists($invoice->receipt_path), 404, 'Receipt not available yet.');

        return response()->download($invoice->receipt_path);
    }
}
