@extends('layouts.admin')

@section('title', 'Payments')
@section('page-title', 'Payment Transactions')
@section('page-subtitle', 'View all PesaPal payment transactions')

@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Total</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Completed</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Pending</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Failed</p>
        <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Revenue</p>
        <p class="text-2xl font-bold text-black">TZS {{ number_format($stats['revenue']) }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Reference, description, tracking ID..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                <option value="">All</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.payments.index') }}" class="btn-outline text-sm px-4 py-2">Clear</a>
        @endif
    </form>
</div>

{{-- Payments Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left">Reference</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Amount</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Method</th>
                    <th class="px-4 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-900">{{ $payment->merchant_reference }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700">{{ Str::limit($payment->description, 40) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-bold text-gray-900">TZS {{ number_format($payment->amount) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500">{{ class_basename($payment->paymentable_type) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($payment->status === 'completed')
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Completed
                                </span>
                            @elseif($payment->status === 'pending')
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-yellow-700 bg-yellow-50 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span> Pending
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> {{ ucfirst($payment->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500">{{ $payment->payment_method ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-500">{{ $payment->created_at->format('d M Y, H:i') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <p class="text-gray-400 text-sm">No payments found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
