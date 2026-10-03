@extends('layouts.admin')

@section('title', 'Add Scanner Check')
@section('page-title', 'Add Scanner Check')
@section('page-subtitle', 'Configure a new scanner check')

@section('content')

<form method="POST" action="{{ route('admin.scanner-checks.store') }}" class="space-y-5">
    @csrf

    <x-admin.note tone="info" title="Checks are the building blocks of a scan">
        Each check tests one thing and carries a weight. When it passes it awards its points,
        and the total across all checks becomes the health score. Write the client-facing
        wording in plain language — that text is what appears on the report.
    </x-admin.note>

    <x-admin.card title="Check configuration" icon="checklist">
        @include('admin.scanner-checks._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Create check
        </button>
        <a href="{{ route('admin.scanner-checks.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection