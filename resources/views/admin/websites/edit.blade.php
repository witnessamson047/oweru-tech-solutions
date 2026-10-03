@extends('layouts.admin')

@section('title', 'Edit Website')
@section('page-title', 'Edit Website')
@section('page-subtitle', $website->business_name)

@section('content')

<form method="POST" action="{{ route('admin.websites.update', $website) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <x-admin.card title="Website details" icon="globe">
        @include('admin.websites._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" /> Save changes
        </button>
        <a href="{{ route('admin.websites.show', $website) }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

{{-- Delete lives outside the update form: a <form> inside a <form> is invalid. --}}
<div class="mt-4 flex justify-end">
    <x-admin.action :action="route('admin.websites.destroy', $website)"
                    method="DELETE" variant="danger" icon="trash"
                    :confirm="'Delete the website &quot;'.$website->business_name.'&quot; and all its scans? This cannot be undone.'">
        Delete this website
    </x-admin.action>
</div>

@endsection