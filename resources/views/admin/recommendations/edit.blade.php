@extends('layouts.admin')

@section('title', 'Edit Recommendation Mapping')
@section('page-title', 'Edit Recommendation Mapping')
@section('page-subtitle', $recommendation->check_name)

@section('content')

<form method="POST" action="{{ route('admin.recommendations.update', $recommendation) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-5">
            <x-admin.card :title="'Mapping details'" :icon="'bulb'">
                @include('admin.recommendations._form')
            </x-admin.card>
        </div>

        <div class="space-y-5">
            <x-admin.card title="At a glance" icon="info">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="admin-muted">Status</dt>
                        <dd>
                            <x-admin.badge :variant="$recommendation->active ? 'success' : 'neutral'">
                                {{ $recommendation->active ? 'Active' : 'Inactive' }}
                            </x-admin.badge>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="admin-muted">Priority</dt>
                        <dd>
                            <x-admin.badge :variant="['high' => 'danger', 'medium' => 'warning', 'low' => 'neutral'][$recommendation->priority] ?? 'neutral'">
                                {{ ucfirst($recommendation->priority) }}
                            </x-admin.badge>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="admin-muted">Created</dt>
                        <dd class="admin-tabular">{{ optional($recommendation->created_at)->format('j M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            <x-admin.card title="Danger zone" icon="warning">
                <p class="text-sm admin-muted">
                    Deleting removes this mapping permanently. Future scans that fail
                    <span class="font-semibold">{{ $recommendation->check_name }}</span>
                    will no longer suggest a solution.
                </p>
            </x-admin.card>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 sticky bottom-0 py-3">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Save changes
        </button>
        <a href="{{ route('admin.recommendations.index') }}" class="admin-btn-ghost">Cancel</a>
        <a href="{{ route('admin.recommendations.index') }}" class="admin-btn-ghost !ml-auto">Back to list</a>
    </div>
</form>

{{-- Delete sits outside the update form: a <form> inside a <form> is invalid
     and browsers drop the inner one, so the button would stop working. --}}
<div class="mt-4 flex justify-end">
    <x-admin.action :action="route('admin.recommendations.destroy', $recommendation)"
                    method="DELETE" variant="danger" icon="trash"
                    :confirm="'Delete the mapping for '.$recommendation->check_name.'? This cannot be undone.'">
        Delete mapping
    </x-admin.action>
</div>

@endsection