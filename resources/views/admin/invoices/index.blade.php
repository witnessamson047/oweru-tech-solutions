@extends('layouts.admin')

@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('page-subtitle', 'Deposit invoicing with automatic receipts once an invoice is paid')

@section('content')

@php
    use App\Models\Invoice;

    $statusVariant = fn ($status) => match ($status) {
        Invoice::STATUS_PAID => 'success',
        Invoice::STATUS_PART_PAID => 'info',
        Invoice::STATUS_ISSUED => 'warning',
        Invoice::STATUS_CANCELLED => 'danger',
        default => 'neutral',
    };
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
        <x-admin.stat label="Pipeline value" value="TZS {{ number_format($stats['total_value']) }}" icon="document-text" />
        <x-admin.stat label="Collected" value="TZS {{ number_format($stats['collected']) }}" icon="money" />
        <x-admin.stat label="Outstanding" value="TZS {{ number_format($stats['outstanding']) }}" icon="warning" />
        <x-admin.stat label="Awaiting deposit" :value="$stats['awaiting_deposit']" icon="clock" />
        <x-admin.stat label="Fully paid" :value="$stats['paid']" icon="check-circle" />
    </div>

    <x-admin.note tone="info" title="How invoicing works">
        Customers pay a deposit (default 50%) before work begins. A receipt is
        generated automatically once the balance is settled. Invoices are created
        automatically when an enquiry reaches <strong>proposal_sent</strong>, or you can
        add one by hand.
    </x-admin.note>

    <x-admin.card :padded="false">
        <x-slot:title>All invoices</x-slot:title>
        <x-slot:subtitle>{{ $invoices->total() }} {{ Str::plural('invoice', $invoices->total()) }} matching</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.invoices.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" /> New invoice
            </a>
        </x-slot:actions>

        <form method="GET" class="admin-filters" role="search">
            <x-admin.search :value="request('search')" placeholder="Invoice number or customer…" />

            <div class="field">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status">
                    <option value="">Any status</option>
                    @foreach (Invoice::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === (string) $key)>{{ ucfirst(str_replace('_', ' ', $label)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="spacer"></div>
            <div class="flex items-center gap-2">
                <button type="submit" class="admin-btn">
                    <x-admin.icon name="filter" class="w-4 h-4" /> Filter
                </button>
                @if (request()->query())
                    <a href="{{ route('admin.invoices.index') }}" class="admin-btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Deposit</th>
                        <th class="text-right">Paid</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="admin-inline-link font-mono text-xs">{{ $invoice->number }}</a>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $invoice->title }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}</div>
                                <div class="text-xs text-gray-500">{{ $invoice->enquiry->email }}</div>
                            </td>
                            <td class="text-right font-semibold text-gray-900 admin-tabular">TZS {{ number_format($invoice->total) }}</td>
                            <td class="text-right text-gray-600 admin-tabular">{{ $invoice->deposit_percent }}% · {{ number_format($invoice->deposit_due) }}</td>
                            <td class="text-right font-semibold admin-tabular" style="color: var(--admin-success)">TZS {{ number_format($invoice->amount_paid) }}</td>
                            <td>
                                <x-admin.badge :variant="$statusVariant($invoice->status)">{{ $invoice->status_label }}</x-admin.badge>
                                @if ($invoice->is_overdue)
                                    <div class="mt-1">
                                        <x-admin.badge variant="danger" :dot="false">{{ $invoice->days_overdue }}d overdue</x-admin.badge>
                                    </div>
                                @endif
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.invoices.pdf', $invoice)" icon="download" title="Download PDF">PDF</x-admin.action>
                                    @if ($invoice->receipt_path)
                                        <x-admin.action :href="route('admin.invoices.receipt', $invoice)" icon="receipt" title="Download receipt">Receipt</x-admin.action>
                                    @endif
                                    @if ($invoice->status === Invoice::STATUS_DRAFT)
                                        <x-admin.action :action="route('admin.invoices.issue', $invoice)" method="POST"
                                                        icon="play" variant="gold" title="Issue this invoice">Issue</x-admin.action>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="document-text" title="No invoices found"
                                       text="Invoices are created automatically when an enquiry reaches proposal sent, or you can add one above." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$invoices" label="invoices" />
    </x-admin.card>
</div>

@endsection