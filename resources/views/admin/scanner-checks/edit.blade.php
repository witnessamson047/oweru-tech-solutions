@extends('layouts.admin')

@section('title', 'Edit Scanner Check')
@section('page-title', 'Edit Scanner Check')
@section('page-subtitle', $scannerCheck->name)

@section('content')

<form method="POST" action="{{ route('admin.scanner-checks.update', $scannerCheck) }}" class="space-y-5">
    @csrf
    @method('PATCH')

    <x-admin.card title="Check configuration" icon="checklist">
        @include('admin.scanner-checks._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Update check
        </button>
        <a href="{{ route('admin.scanner-checks.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection