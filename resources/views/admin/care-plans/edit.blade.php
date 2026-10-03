@extends('layouts.admin')

@section('title', 'Edit Care Plan')
@section('page-title', 'Edit Care Plan')
@section('page-subtitle', $carePlan->name)

@section('content')

<form method="POST" action="{{ route('admin.care-plans.update', $carePlan) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <x-admin.card title="Plan details" icon="care-plan">
        @include('admin.care-plans._form')
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Save changes
        </button>
        <a href="{{ route('admin.care-plans.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

{{-- Delete sits outside the update form: a <form> inside a <form> is invalid
     and browsers drop the inner one, so the button would stop working. --}}
<div class="mt-4 flex justify-end">
    <x-admin.action :action="route('admin.care-plans.destroy', $carePlan)"
                    method="DELETE" variant="danger" icon="trash"
                    :confirm="'Delete the care plan &quot;'.$carePlan->name.'&quot;? This cannot be undone.'">
        Delete this plan
    </x-admin.action>
</div>

@endsection