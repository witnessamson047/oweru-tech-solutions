@extends('layouts.admin')

@section('title', 'New Care Plan')
@section('page-title', 'New Care Plan')
@section('page-subtitle', 'A recurring monthly plan clients can subscribe to')

@section('content')

<form method="POST" action="{{ route('admin.care-plans.store') }}" class="space-y-5">
    @csrf

    <x-admin.note tone="info" title="Care plans are recurring revenue">
        A care plan is a monthly subscription — hosting, maintenance, support hours —
        that keeps a client on retainer after the build is done. Price it so the
        monthly fee feels small next to the value of keeping their site healthy.
    </x-admin.note>

    <x-admin.card title="Plan details" icon="care-plan">
        @include('admin.care-plans._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Create care plan
        </button>
        <a href="{{ route('admin.care-plans.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection