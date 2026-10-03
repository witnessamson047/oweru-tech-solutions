@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payment Transactions')
@section('page-subtitle', 'Every PesaPal transaction, and how it reconciles against invoices')

@section('content')

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
        <x-admin.stat label="All transactions" :value="$stats['total']" icon="receipt" />
        <x-admin.stat label="Completed" :value="$stats['completed']" icon="check-circle"
                      :href="route('admin.payments.index', ['status' => 'completed'])" />
        <x-admin.stat label="Pending" :value="$stats['pending']" icon="clock"
                      :href="route('admin.payments.index', ['status' => 'pending'])" />
        <x-admin.stat label="Failed" :value="$stats['failed']" icon="warning"
                      :href="route('admin.payments.index', ['status' => 'failed'])" />
        <x-admin.stat label="Collected" value="TZS {{ number_format($stats['revenue']) }}" icon="money" featured />
    </div>

    <x-admin.note tone="info" title="Payments are recorded by the gateway">
        Rows appear here when PesaPal calls back. A payment that looks stuck as
        <strong>Pending</strong> may need a manual reconcile — check the invoice before
        chasing the customer.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.payments.index')" submit-label="Filter">
        <x-admin.search :value="request('search')" placeholder="Reference, description or tracking ID…" />

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Transactions</x-slot:title>
        <x-slot:subtitle>{{ $payments->total() }} {{ Str::plural('transaction', $payments->total()) }} matching</x-slot:subtitle>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td><span class="font-mono text-xs text-gray-900">{{ $payment->merchant_reference }}</span></td>
                            <td>
                                <span class="admin-muted block max-w-[16rem] truncate" title="{{ $payment->description }}">
                                    {{ $payment->description ?: '—' }}
                                </span>
                            </td>
                            <td class="text-right font-semibold text-gray-900 admin-tabular">TZS {{ number_format($payment->amount) }}</td>
                            <td class="text-gray-600">{{ class_basename($payment->paymentable_type) ?: '—' }}</td>
                            <td>
                                <x-admin.badge :variant="$payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger')">
                                    {{ ucfirst($payment->status) }}
                                </x-admin.badge>
                            </td>
                            <td class="text-gray-600">{{ $payment->payment_method ?? '—' }}</td>
                            <td>
                                <span class="text-gray-700">{{ $payment->created_at->format('d M Y, H:i') }}</span>
                                <span class="block text-xs text-gray-400">{{ $payment->created_at->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="receipt" title="No payments found"
                                       text="Transactions appear here once the gateway confirms them. Clear the filters to see the full ledger." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$payments" label="transactions" />
    </x-admin.card>
</div>

@endsection