@extends('layouts.admin')

@section('title', 'Add Website')
@section('page-title', 'Add Website')
@section('page-subtitle', 'Track a new website for scanning and monitoring')

@section('content')

<form method="POST" action="{{ route('admin.websites.store') }}" class="space-y-5">
    @csrf

    <x-admin.note tone="info" title="Every lead starts with a site">
        Add the client's address once and the scanner, reports and pitch recommendations all
        work from it. Tracking a site you have not sold to yet is fine — that is how the
        health scan becomes your opening pitch.
    </x-admin.note>

    <x-admin.card title="Website details" icon="globe">
        @include('admin.websites._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Add website
        </button>
        <a href="{{ route('admin.websites.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection