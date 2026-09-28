@extends('layouts.admin')

@section('title', 'Invoices — Oweru Admin')
@section('page-title', 'Invoices')
@section('page-subtitle', '50% deposit invoicing with automatic receipts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoices</h1>
            <p class="text-sm text-gray-500 mt-1">Customers pay a deposit (default 50%) before work begins. A receipt is generated automatically once everything is paid.</p>
        </div>
        <a href="{{ route('admin.invoices.create') }}" class="btn-accent text-sm">＋ New Invoice</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Pipeline Value</p>
            <p class="text-xl font-extrabold text-gray-900 mt-1">TZS {{ number_format($stats['total_value']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Collected</p>
            <p class="text-xl font-extrabold text-green-600 mt-1">TZS {{ number_format($stats['collected']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Outstanding</p>
            <p class="text-xl font-extrabold text-red-600 mt-1">TZS {{ number_format($stats['outstanding']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Awaiting Deposit</p>
            <p class="text-xl font-extrabold text-yellow-600 mt-1">{{ $stats['awaiting_deposit'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Fully Paid</p>
            <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $stats['paid'] }}</p>
        </div>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-gray-900">All Invoices</h2>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Search number, customer…" value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm w-56">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\Invoice::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === (string) $key ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $label)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-outline text-xs px-3 py-2">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Invoice</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-right">Total</th>
                        <th class="px-6 py-3 text-right">Deposit</th>
                        <th class="px-6 py-3 text-right">Paid</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-semibold text-gray-900 hover:text-yellow-600 font-mono text-xs">{{ $invoice->number }}</a>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $invoice->title }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <div class="font-medium text-gray-900">{{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}</div>
                                <div class="mt-0.5">{{ $invoice->enquiry->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">TZS {{ number_format($invoice->total) }}</td>
                            <td class="px-6 py-4 text-right text-gray-600">{{ $invoice->deposit_percent }}% · TZS {{ number_format($invoice->deposit_due) }}</td>
                            <td class="px-6 py-4 text-right text-green-600 font-medium">TZS {{ number_format($invoice->amount_paid) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = match($invoice->status) {
                                        \App\Models\Invoice::STATUS_PAID => 'badge-success',
                                        \App\Models\Invoice::STATUS_PART_PAID => 'badge-info',
                                        \App\Models\Invoice::STATUS_ISSUED => 'badge-warning',
                                        \App\Models\Invoice::STATUS_CANCELLED => 'badge-gray',
                                        default => 'badge-gray',
                                    };
                                @endphp
                                <span class="badge {{ $badge }} text-[10px] uppercase">{{ $invoice->status_label }}</span>
                                @if($invoice->is_overdue)
                                    <div class="mt-1"><span class="badge text-[10px] uppercase" style="background:#fef2f2;color:#b91c1c;">⚠ {{ $invoice->days_overdue }}d overdue</span></div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">PDF</a>
                                    @if($invoice->receipt_path)
                                        <a href="{{ route('admin.invoices.receipt', $invoice) }}" class="text-xs font-medium text-green-600 hover:text-green-700">Receipt</a>
                                    @endif
                                    @if($invoice->status === \App\Models\Invoice::STATUS_DRAFT)
                                        <form method="POST" action="{{ route('admin.invoices.issue', $invoice) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">Issue</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                No invoices yet — invoices are auto-created when an enquiry reaches <strong>proposal_sent</strong>, or create one manually.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
