@extends('layouts.admin')

@section('title', 'Invoice ' . $invoice->number . ' — Oweru Admin')
@section('page-title', 'Invoice ' . $invoice->number)
@section('page-subtitle', $invoice->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700">← Back to Invoices</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1 font-mono">{{ $invoice->number }}</h1>
            <p class="text-sm text-gray-500">{{ $invoice->title }}</p>
        </div>
        @php
            $badge = match($invoice->status) {
                \App\Models\Invoice::STATUS_PAID => 'badge-success',
                \App\Models\Invoice::STATUS_PART_PAID => 'badge-info',
                \App\Models\Invoice::STATUS_ISSUED => 'badge-warning',
                default => 'badge-gray',
            };
        @endphp
        <span class="badge {{ $badge }} text-xs uppercase">{{ $invoice->status_label }}</span>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total</p>
            <p class="text-lg font-extrabold text-gray-900 mt-1">TZS {{ number_format($invoice->total) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Deposit ({{ $invoice->deposit_percent }}%)</p>
            <p class="text-lg font-extrabold text-yellow-600 mt-1">TZS {{ number_format($invoice->deposit_due) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Paid</p>
            <p class="text-lg font-extrabold text-green-600 mt-1">TZS {{ number_format($invoice->amount_paid) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Balance</p>
            <p class="text-lg font-extrabold {{ $invoice->balance_due > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">TZS {{ number_format($invoice->balance_due) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-900">Details</h2>
            </div>
            <dl class="divide-y divide-gray-50">
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Customer</dt>
                    <dd class="text-sm text-gray-900">
                        <a href="{{ route('admin.enquiries.show', $invoice->enquiry) }}" class="text-yellow-600 hover:text-yellow-700">
                            {{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}
                        </a>
                    </dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Email</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->enquiry->email }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Issued</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->issued_at?->format('d M Y, H:i') ?? '— not yet issued —' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Deposit due date</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->due_at?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Receipt</dt>
                    <dd class="text-sm text-gray-900">
                        @if($invoice->receipt_generated_at)
                            ✓ Generated {{ $invoice->receipt_generated_at->format('d M Y, H:i') }}
                        @else
                            Auto-generates when fully paid
                        @endif
                    </dd>
                </div>
                <div class="px-6 py-3 grid grid-cols-2 gap-2">
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Reminders</dt>
                    <dd class="text-sm text-gray-900">
                        @if($invoice->is_overdue)
                            <span class="text-red-600 font-semibold">⚠ Deposit {{ $invoice->days_overdue }} day{{ $invoice->days_overdue === 1 ? '' : 's' }} overdue</span>
                        @endif
                        <span class="text-gray-600">
                            {{ $invoice->reminders_sent }} reminder{{ $invoice->reminders_sent === 1 ? '' : 's' }} sent
                            @if($invoice->last_reminder_sent_at)
                                · last {{ $invoice->last_reminder_sent_at->diffForHumans() }}
                            @endif
                        </span>
                    </dd>
                </div>
                @if($invoice->description)
                    <div class="px-6 py-3">
                        <dt class="text-xs font-semibold text-gray-500 uppercase mb-1">Terms</dt>
                        <dd class="text-sm text-gray-600 leading-relaxed">{{ $invoice->description }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-900">Payment History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($invoice->completedPayments as $payment)
                            <tr>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $payment->paid_at?->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-xs text-gray-900">{{ $payment->kind_label }}</td>
                                <td class="px-4 py-2 text-xs text-green-600 font-semibold text-right">TZS {{ number_format($payment->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-xs text-gray-400">
                                    @if($invoice->status === \App\Models\Invoice::STATUS_DRAFT)
                                        Issue the invoice so the customer can pay.
                                    @elseif($invoice->is_fully_paid)
                                        Fully paid ✓
                                    @else
                                        Awaiting {{ $invoice->is_deposit_paid ? 'balance' : 'deposit' }} payment (TZS {{ number_format($invoice->next_payment_amount) }}).
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Actions</h2>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn-outline text-sm">⬇ Invoice PDF</a>

            @if($invoice->status === \App\Models\Invoice::STATUS_DRAFT)
                <form method="POST" action="{{ route('admin.invoices.issue', $invoice) }}">
                    @csrf
                    <button type="submit" class="btn-accent text-sm">📤 Issue & Email Customer</button>
                </form>
            @endif

            @if($invoice->is_fully_paid)
                <a href="{{ route('admin.invoices.receipt', $invoice) }}" class="btn-outline text-sm">🧾 Receipt PDF</a>
            @endif

            @if(!in_array($invoice->status, [\App\Models\Invoice::STATUS_PAID, \App\Models\Invoice::STATUS_CANCELLED]))
                <form method="POST" action="{{ route('admin.invoices.cancel', $invoice) }}"
                      onsubmit="return confirm('Cancel this invoice? This cannot be undone.')">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">Cancel Invoice</button>
                </form>
            @endif

            @if($invoice->is_deposit_paid && !$invoice->is_fully_paid)
                <span class="text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
                    Deposit received — send the customer the
                    <a href="{{ route('payment.checkout', ['type' => 'invoice-full', 'id' => $invoice->id]) }}" class="text-yellow-600 hover:text-yellow-700 font-medium">balance payment link</a>
                </span>
            @endif
        </div>
    </div>
</div>
@endsection
