@extends('layouts.admin')

@section('title', 'Invoice '.$invoice->number.' — Oweru Admin')
@section('page-title', 'Invoice '.$invoice->number)
@section('page-subtitle', $invoice->title)

@section('content')

@php
    $statusVariant = match ($invoice->status) {
        \App\Models\Invoice::STATUS_PAID => 'success',
        \App\Models\Invoice::STATUS_PART_PAID => 'info',
        \App\Models\Invoice::STATUS_ISSUED => 'warning',
        default => 'neutral',
    };
@endphp

<div class="space-y-5">

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="admin-inline-link text-xs">
                <x-admin.icon name="arrow-left" class="mr-1 inline w-3.5 h-3.5" /> Back to invoices
            </a>
            <h1 class="mt-1 font-mono text-2xl font-bold text-gray-900">{{ $invoice->number }}</h1>
            <p class="admin-muted text-sm">{{ $invoice->title }}</p>
        </div>
        <x-admin.badge :variant="$statusVariant" class="uppercase">{{ $invoice->status_label }}</x-admin.badge>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.stat label="Total" icon="money" :value="'TZS '.number_format($invoice->total)" />
        <x-admin.stat label="Deposit ({{ $invoice->deposit_percent }}%)" icon="credit-card" :value="'TZS '.number_format($invoice->deposit_due)" />
        <x-admin.stat label="Paid" icon="check-circle" :value="'TZS '.number_format($invoice->amount_paid)" />
        <x-admin.stat label="Balance" icon="receipt" :value="'TZS '.number_format($invoice->balance_due)"
                      :hint="$invoice->balance_due > 0 ? 'Outstanding' : 'Settled'" />
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        <x-admin.card title="Details" icon="document-text" :padded="false">
            <dl class="divide-y divide-gray-50">
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Customer</dt>
                    <dd class="text-sm text-gray-900">
                        <a href="{{ route('admin.enquiries.show', $invoice->enquiry) }}" class="admin-inline-link">
                            {{ $invoice->enquiry->business_name ?: $invoice->enquiry->name }}
                        </a>
                    </dd>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Email</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->enquiry->email }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Issued</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->issued_at?->format('d M Y, H:i') ?? 'Not yet issued' }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Deposit due date</dt>
                    <dd class="text-sm text-gray-900">{{ $invoice->due_at?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Receipt</dt>
                    <dd class="text-sm text-gray-900">
                        @if ($invoice->receipt_generated_at)
                            Generated {{ $invoice->receipt_generated_at->format('d M Y, H:i') }}
                        @else
                            Auto-generates when fully paid
                        @endif
                    </dd>
                </div>
                <div class="grid grid-cols-2 gap-2 px-5 py-3">
                    <dt class="admin-label">Reminders</dt>
                    <dd class="text-sm text-gray-900">
                        @if ($invoice->is_overdue)
                            <span class="font-semibold" style="color: var(--admin-danger)">Deposit {{ $invoice->days_overdue }} {{ Str::plural('day', $invoice->days_overdue) }} overdue</span><br>
                        @endif
                        <span class="text-gray-600">
                            {{ $invoice->reminders_sent }} {{ Str::plural('reminder', $invoice->reminders_sent) }} sent
                            @if ($invoice->last_reminder_sent_at) · last {{ $invoice->last_reminder_sent_at->diffForHumans() }} @endif
                        </span>
                    </dd>
                </div>
                @if ($invoice->description)
                    <div class="px-5 py-3">
                        <dt class="admin-label mb-1">Terms</dt>
                        <dd class="text-sm leading-relaxed text-gray-600">{{ $invoice->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card title="Payment history" icon="credit-card" :padded="false">
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
                        @forelse ($invoice->completedPayments as $payment)
                            <tr>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $payment->paid_at?->format('d M Y') }}</td>
                                <td class="px-4 py-2 text-xs text-gray-900">{{ $payment->kind_label }}</td>
                                <td class="px-4 py-2 text-right text-xs font-semibold text-green-600 admin-tabular">TZS {{ number_format($payment->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-xs text-gray-400">
                                    @if ($invoice->status === \App\Models\Invoice::STATUS_DRAFT)
                                        Issue the invoice so the customer can pay.
                                    @elseif ($invoice->is_fully_paid)
                                        Fully paid.
                                    @else
                                        Awaiting {{ $invoice->is_deposit_paid ? 'balance' : 'deposit' }} payment (TZS {{ number_format($invoice->next_payment_amount) }}).
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    <x-admin.card title="Actions" icon="play" :padded="true">
        <div class="flex flex-wrap items-center gap-3">
            <x-admin.action :href="route('admin.invoices.pdf', $invoice)" icon="download">Invoice PDF</x-admin.action>

            @if ($invoice->status === \App\Models\Invoice::STATUS_DRAFT)
                <x-admin.action :action="route('admin.invoices.issue', $invoice)" variant="primary" icon="mail">
                    Issue &amp; email customer
                </x-admin.action>
            @endif

            @if ($invoice->is_fully_paid)
                <x-admin.action :href="route('admin.invoices.receipt', $invoice)" icon="receipt">Receipt PDF</x-admin.action>
            @endif

            @if (! in_array($invoice->status, [\App\Models\Invoice::STATUS_PAID, \App\Models\Invoice::STATUS_CANCELLED]))
                <x-admin.action :action="route('admin.invoices.cancel', $invoice)" variant="danger" icon="ban"
                                :confirm="'Cancel this invoice? This cannot be undone.'">
                    Cancel invoice
                </x-admin.action>
            @endif

            @if ($invoice->is_deposit_paid && ! $invoice->is_fully_paid)
                <span class="admin-muted inline-flex items-center gap-1 rounded-lg bg-gray-50 px-3 py-2 text-xs">
                    Deposit received — send the customer the
                    <a href="{{ route('payment.checkout', ['type' => 'invoice-full', 'id' => $invoice->id]) }}" class="admin-inline-link">balance payment link</a>
                </span>
            @endif
        </div>
    </x-admin.card>
</div>

@endsection