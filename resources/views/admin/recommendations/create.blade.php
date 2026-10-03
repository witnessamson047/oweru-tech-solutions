@extends('layouts.admin')

@section('title', 'New Recommendation Mapping')
@section('page-title', 'New Recommendation Mapping')
@section('page-subtitle', 'Connect a scanner failure to an Oweru service you can sell')

@section('content')

<form method="POST" action="{{ route('admin.recommendations.store') }}" class="space-y-5">
    @csrf

    <x-admin.note tone="info" title="How matching works">
        Every failed scanner check looks for a recommendation whose
        <span class="font-semibold">check name matches exactly</span>. Copy the name from
        <a href="{{ route('admin.scanner-checks.index') }}" class="font-semibold underline">Scanner Checks</a>
        so the mapping attaches automatically to future scans.
    </x-admin.note>

    <x-admin.card :title="'Mapping details'" :icon="'bulb'">
        @include('admin.recommendations._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2 sticky bottom-0 py-3">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Create mapping
        </button>
        <a href="{{ route('admin.recommendations.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection