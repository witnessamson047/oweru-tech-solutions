@extends('layouts.admin')

@section('title', 'New Invoice — Oweru Admin')
@section('page-title', 'New Invoice')
@section('page-subtitle', 'Create a deposit invoice for an enquiry')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <a href="{{ route('admin.invoices.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700">← Back to Invoices</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1 mb-6">Create Invoice</h1>

    <form method="POST" action="{{ route('admin.invoices.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Enquiry (customer) *</label>
            <select name="enquiry_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
                <option value="">— select an enquiry —</option>
                @foreach($enquiries as $enquiry)
                    <option value="{{ $enquiry->id }}" {{ old('enquiry_id', $selectedEnquiry?->id) == $enquiry->id ? 'selected' : '' }}>
                        #{{ $enquiry->id }} — {{ $enquiry->business_name ?: $enquiry->name }} ({{ $enquiry->email }})
                        @if($enquiry->package_name) · {{ $enquiry->package_name }} @endif
                    </option>
                @endforeach
            </select>
            @error('enquiry_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Website Redesign & Care Plan"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
            <p class="text-xs text-gray-400 mt-1">Leave blank to use the enquiry's package name.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total (TZS)</label>
            <input type="number" name="total" min="0" step="1000" value="{{ old('total') }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
            <p class="text-xs text-gray-400 mt-1">Leave blank to use the package price (or the default quote of TZS 1,500,000).</p>
            @error('total') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deposit required before work starts (%)</label>
            <input type="number" name="deposit_percent" min="0" max="100" value="{{ old('deposit_percent', 50) }}" required
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
            <p class="text-xs text-gray-400 mt-1">Default is 50% — the customer pays this before any work begins.</p>
            @error('deposit_percent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="issue_now" value="1" id="issue_now" checked
                   class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
            <label for="issue_now" class="text-sm text-gray-700">Issue immediately (emails the invoice PDF with deposit payment link)</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-accent">Create Invoice</button>
            <a href="{{ route('admin.invoices.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
