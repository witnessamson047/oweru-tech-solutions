@extends('layouts.admin')

@section('title', 'Edit Package')
@section('page-title', 'Edit Service Package')
@section('page-subtitle', $package->name)

@section('content')

<form method="POST" action="{{ route('admin.packages.update', $package) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <x-admin.card title="Package details" icon="package">
        @include('admin.packages._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Save changes
        </button>
        <a href="{{ route('admin.packages.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection