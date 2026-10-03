@extends('layouts.admin')

@section('title', 'New Invoice — Oweru Admin')
@section('page-title', 'New Invoice')
@section('page-subtitle', 'Create a deposit invoice for an enquiry')

@section('content')

<div class="max-w-2xl space-y-5">

    <a href="{{ route('admin.invoices.index') }}" class="admin-inline-link text-xs">
        <x-admin.icon name="arrow-left" class="mr-1 inline w-3.5 h-3.5" /> Back to invoices
    </a>

    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-5">
        @csrf

        <x-admin.note tone="info" title="Deposit first, work after">
            Issue the invoice with a deposit percentage, then the customer pays it before work
            begins. The balance payment link is sent once the deposit clears.
        </x-admin.note>

        <x-admin.card title="Invoice details" icon="receipt">
            <div class="space-y-4">
                <x-admin.field name="enquiry_id" label="Enquiry (customer)" required
                               hint="The lead this invoice is for.">
                    <select name="enquiry_id" required>
                        <option value="">— select an enquiry —</option>
                        @foreach ($enquiries as $enquiry)
                            <option value="{{ $enquiry->id }}" @selected(old('enquiry_id', $selectedEnquiry?->id) == $enquiry->id)>
                                #{{ $enquiry->id }} — {{ $enquiry->business_name ?: $enquiry->name }} ({{ $enquiry->email }})@if($enquiry->package_name) · {{ $enquiry->package_name }}@endif
                            </option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field name="title" label="Title"
                               hint="Leave blank to use the enquiry's package name.">
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="e.g. Website Redesign & Care Plan">
                </x-admin.field>

                <x-admin.field name="total" label="Total (TZS)"
                               hint="Leave blank to use the package price (or the default quote of TZS 1,500,000).">
                    <input type="number" name="total" min="0" step="1000" value="{{ old('total') }}">
                </x-admin.field>

                <x-admin.field name="deposit_percent" label="Deposit required before work starts (%)" required
                               hint="Default is 50% — the customer pays this before any work begins.">
                    <input type="number" name="deposit_percent" min="0" max="100" required
                           value="{{ old('deposit_percent', 50) }}">
                </x-admin.field>

                <label class="admin-checkbox">
                    <input type="hidden" name="issue_now" value="0">
                    <input type="checkbox" name="issue_now" value="1" @checked(old('issue_now', true))>
                    Issue immediately — emails the invoice PDF with the deposit payment link
                </label>
            </div>
        </x-admin.card>

        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="admin-btn">
                <x-admin.icon name="check" class="w-4 h-4" /> Create invoice
            </button>
            <a href="{{ route('admin.invoices.index') }}" class="admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@endsection