@extends('layouts.admin')

@section('title', 'Enquiry Details')
@section('page-title', 'Enquiry: ' . $enquiry->name)
@section('page-subtitle', $enquiry->business_name)

@section('content')

@php
    use App\Models\Invoice;

    $stageVariant = fn ($stage) => match ($stage) {
        'qualified' => 'info',
        'diagnostic_paid' => 'warning',
        'proposal_sent' => 'gold',
        'won' => 'success',
        'lost' => 'danger',
        default => 'neutral',
    };
    $invoiceVariant = fn ($status) => match ($status) {
        Invoice::STATUS_PAID => 'success',
        Invoice::STATUS_PART_PAID => 'info',
        Invoice::STATUS_ISSUED => 'warning',
        Invoice::STATUS_CANCELLED => 'danger',
        default => 'neutral',
    };
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    <div class="lg:col-span-2 space-y-5">

        <x-admin.card title="Contact information" icon="inbox">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="admin-label">Name</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $enquiry->name }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Business</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $enquiry->business_name }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Email</dt>
                    <dd class="mt-1"><a href="mailto:{{ $enquiry->email }}" class="admin-inline-link">{{ $enquiry->email }}</a></dd>
                </div>
                <div>
                    <dt class="admin-label">Phone</dt>
                    <dd class="mt-1"><a href="tel:{{ $enquiry->phone }}" class="admin-inline-link">{{ $enquiry->phone }}</a></dd>
                </div>
                <div>
                    <dt class="admin-label">Country</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $enquiry->country }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Source</dt>
                    <dd class="mt-1"><x-admin.badge variant="info">{{ ucfirst($enquiry->source ?? 'direct') }}</x-admin.badge></dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card title="Project details" icon="checklist">
            <dl class="space-y-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-gray-500">Package interest</dt>
                    <dd class="font-semibold text-gray-900">{{ $enquiry->package_name ?? 'Not specified' }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-gray-500">Budget range</dt>
                    <dd class="font-semibold text-gray-900">{{ $enquiry->budget_range ? str_replace('_', ' ', ucfirst($enquiry->budget_range)) : 'Not specified' }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-gray-500">Required date</dt>
                    <dd class="font-semibold text-gray-900">{{ $enquiry->required_date ? \Carbon\Carbon::parse($enquiry->required_date)->format('d M Y') : 'Not specified' }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-gray-500">Current cost / impact</dt>
                    <dd class="font-semibold text-gray-900">{{ $enquiry->current_cost ?: 'Not specified' }}</dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card title="Problem description" icon="document-text">
            <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $enquiry->problem_description }}</p>
        </x-admin.card>

        <x-admin.card :padded="true">
            <x-slot:title>Invoices</x-slot:title>
            <x-slot:subtitle>Deposit invoicing for this enquiry</x-slot:subtitle>
            <x-slot:actions>
                <a href="{{ route('admin.invoices.create', ['enquiry' => $enquiry->id]) }}" class="admin-btn-ghost">
                    <x-admin.icon name="plus" class="w-4 h-4" /> New invoice
                </a>
            </x-slot:actions>

            @if ($enquiry->invoices->count())
                <div class="space-y-3">
                    @foreach ($enquiry->invoices as $invoice)
                        <div class="rounded-xl border border-gray-100 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="admin-inline-link font-mono text-xs">{{ $invoice->number }}</a>
                                <x-admin.badge :variant="$invoiceVariant($invoice->status)">{{ $invoice->status_label }}</x-admin.badge>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                                <div><span class="text-gray-500">Total:</span> <span class="font-semibold admin-tabular">TZS {{ number_format($invoice->total) }}</span></div>
                                <div><span class="text-gray-500">Deposit ({{ $invoice->deposit_percent }}%):</span> <span class="font-semibold admin-tabular">TZS {{ number_format($invoice->deposit_due) }}</span></div>
                                <div><span class="text-gray-500">Paid:</span> <span class="font-semibold admin-tabular" style="color: var(--admin-success)">TZS {{ number_format($invoice->amount_paid) }}</span></div>
                            </div>
                            <div class="mt-3 flex items-center gap-3 text-xs">
                                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="admin-muted hover:underline">PDF</a>
                                @if ($invoice->receipt_path)
                                    <a href="{{ route('admin.invoices.receipt', $invoice) }}" class="admin-inline-link">Receipt</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">
                    No invoice yet. One is created automatically when the stage moves to
                    <strong>proposal_sent</strong> ({{ config('owers.invoice.deposit_percent') }}% deposit required
                    before work starts), or
                    <a href="{{ route('admin.invoices.create', ['enquiry' => $enquiry->id]) }}" class="admin-inline-link">create one now</a>.
                </p>
            @endif
        </x-admin.card>

        <x-admin.card title="Internal notes" icon="document-text"
                      subtitle="Only staff can see these.">
            <form method="POST" action="{{ route('admin.enquiries.notes', $enquiry) }}" class="space-y-3">
                @csrf
                <x-admin.field name="notes" label="Notes">
                    <textarea name="notes" rows="4" placeholder="Add internal notes…">{{ $enquiry->notes }}</textarea>
                </x-admin.field>
                <div class="flex justify-end">
                    <button type="submit" class="admin-btn">Save notes</button>
                </div>
            </form>
        </x-admin.card>
    </div>

    <div class="space-y-5">

        <x-admin.card title="Pipeline stage" icon="pipeline">
            <x-admin.badge :variant="$stageVariant($enquiry->stage)" class="mb-4 text-sm">
                {{ str_replace('_', ' ', ucfirst($enquiry->stage)) }}
            </x-admin.badge>

            <form method="POST" action="{{ route('admin.enquiries.stage', $enquiry) }}" class="space-y-2">
                @csrf
                @method('PATCH')
                @foreach (['new', 'qualified', 'diagnostic_paid', 'proposal_sent', 'won', 'lost'] as $stage)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg p-2 transition hover:bg-gray-50">
                        <input type="radio" name="stage" value="{{ $stage }}" @checked($enquiry->stage === $stage)>
                        <span class="text-sm text-gray-700">{{ str_replace('_', ' ', ucfirst($stage)) }}</span>
                        @if ($enquiry->stage === $stage)
                            <span class="ml-auto text-xs font-semibold" style="color: var(--gold-dark)">Current</span>
                        @endif
                    </label>
                @endforeach
                <button type="submit" class="admin-btn w-full justify-center">Update stage</button>
            </form>
        </x-admin.card>

        <x-admin.card title="Assigned owner" icon="user">
            <form method="POST" action="{{ route('admin.enquiries.owner', $enquiry) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <x-admin.field name="owner_id" label="Owner">
                    <select name="owner_id">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $user)
                            <option value="{{ $user->id }}" @selected($enquiry->owner_id === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
                <button type="submit" class="admin-btn-ghost w-full justify-center">Assign</button>
            </form>
        </x-admin.card>

        <x-admin.card title="Timeline" icon="clock">
            <dl class="space-y-2 text-xs text-gray-500">
                <div class="flex justify-between"><dt>Created</dt><dd>{{ $enquiry->created_at->diffForHumans() }}</dd></div>
                <div class="flex justify-between"><dt>Last updated</dt><dd>{{ $enquiry->updated_at->diffForHumans() }}</dd></div>
                <div class="flex justify-between"><dt>Stage changed</dt><dd>{{ $enquiry->last_stage_changed_at ? \Carbon\Carbon::parse($enquiry->last_stage_changed_at)->diffForHumans() : '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card title="Actions" icon="play">
            <div class="space-y-2">
                <a href="mailto:{{ $enquiry->email }}" class="admin-btn-ghost w-full justify-center">
                    <x-admin.icon name="mail" class="w-4 h-4" /> Send email
                </a>
                <a href="tel:{{ $enquiry->phone }}" class="admin-btn-ghost w-full justify-center">
                    <x-admin.icon name="phone" class="w-4 h-4" /> Call
                </a>
                @if ($enquiry->source === 'scanner' && $enquiry->scan)
                    <a href="{{ route('admin.scans.show', $enquiry->scan) }}" class="admin-btn-ghost w-full justify-center">
                        <x-admin.icon name="scan" class="w-4 h-4" /> View scan results
                    </a>
                @endif
            </div>
        </x-admin.card>
    </div>
</div>

@endsection