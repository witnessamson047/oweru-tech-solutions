<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoices,
    ) {}

    public function index(Request $request)
    {
        $query = Invoice::with(['enquiry', 'completedPayments']);

        if ($status = $request->input('status')) {
            if (is_numeric($status)) {
                $query->where('status', (int) $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('enquiry', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('business_name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_value' => (float) Invoice::notCancelled()->sum('total'),
            // Sum in SQL. Collecting every invoice into a collection to add two
            // columns grows linearly with the ledger and blocks on large data.
            'collected' => (float) Invoice::notCancelled()->sum('amount_paid'),
            'outstanding' => (float) Invoice::notCancelled()->sum('balance_due'),
            'awaiting_deposit' => Invoice::notCancelled()->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PART_PAID])->count(),
            'paid' => Invoice::where('status', Invoice::STATUS_PAID)->count(),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
    }

    public function create(Request $request)
    {
        $enquiries = Enquiry::orderBy('name')->get();
        $selectedEnquiry = $request->filled('enquiry') ? Enquiry::find($request->input('enquiry')) : null;

        return view('admin.invoices.create', compact('enquiries', 'selectedEnquiry'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'total' => 'nullable|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'deposit_percent' => 'required|integer|min:0|max:100',
        ]);

        $enquiry = Enquiry::findOrFail($validated['enquiry_id']);

        $invoice = $this->invoices->createForEnquiry(
            $enquiry,
            isset($validated['total']) ? (float) $validated['total'] : null,
            $validated['title'] ?? null,
            (int) $validated['deposit_percent'],
        );

        // If created from the proposal flow, issue immediately.
        if ($request->boolean('issue_now')) {
            $this->invoices->issue($invoice);
        }

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->number} created"
                . ($request->boolean('issue_now') ? ' and emailed to the customer.' : '.'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['enquiry.scan', 'completedPayments']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Issue a draft invoice: sets due date and emails the customer the PDF.
     */
    public function issue(Invoice $invoice)
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return redirect()->back()->with('error', 'Only draft invoices can be issued.');
        }

        $this->invoices->issue($invoice);

        return redirect()->back()->with('success', "Invoice {$invoice->number} issued and emailed to {$invoice->enquiry->email}.");
    }

    public function pdf(Invoice $invoice): Response
    {
        return $this->invoices->pdf($invoice)->download("invoice-{$invoice->number}.pdf");
    }

    public function receipt(Invoice $invoice): Response
    {
        if (! $invoice->receipt_path || ! file_exists($invoice->receipt_path)) {
            // (Re)generate on demand for admins — never re-email the customer on download.
            $this->invoices->generateReceipt($invoice, force: $invoice->receipt_generated_at !== null, notify: false);

            if (! $invoice->fresh()->receipt_path || ! file_exists($invoice->fresh()->receipt_path)) {
                return redirect()->back()->with('error', 'Receipt is not available yet — it is generated once the invoice is fully paid.');
            }
        }

        $invoice->increment('receipt_downloads');

        return response()->download($invoice->receipt_path);
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return redirect()->back()->with('error', 'A fully paid invoice cannot be cancelled.');
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        return redirect()->route('admin.invoices.index')->with('success', "Invoice {$invoice->number} cancelled.");
    }
}
