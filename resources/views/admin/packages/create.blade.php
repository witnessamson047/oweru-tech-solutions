@extends('layouts.admin')

@section('title', 'Add Package')
@section('page-title', 'Add Service Package')
@section('page-subtitle', 'Create a new service package')

@section('content')

<form method="POST" action="{{ route('admin.packages.store') }}" class="space-y-5">
    @csrf

    <x-admin.note tone="info" title="Packages are what you quote">
        A package bundles a scope of work into a fix: the price, the delivery time and the
        audience. Keep the description outcome-focused — clients buy the result, not the task list.
    </x-admin.note>

    <x-admin.card title="Package details" icon="package">
        @include('admin.packages._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Create package
        </button>
        <a href="{{ route('admin.packages.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection