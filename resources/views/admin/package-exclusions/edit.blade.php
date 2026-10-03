@extends('layouts.admin')

@section('title', 'Edit Exclusion')
@section('page-title', 'Edit Package Exclusion')
@section('page-subtitle', $packageExclusion->description)

@section('content')

<form method="POST" action="{{ route('admin.package-exclusions.update', $packageExclusion) }}" class="max-w-3xl space-y-5">
    @csrf
    @method('PUT')

    <x-admin.note tone="info" title="What this page controls">
        Exclusions are the "not included" list shown on the public packages page —
        the things a client might assume are covered but are not. Keep each line
        short and specific.
    </x-admin.note>

    <x-admin.card title="Exclusion details" icon="shield-off">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.field name="description" label="Short description" required
                           hint="One line, shown as the headline of the exclusion.">
                <input type="text" name="description" required maxlength="255"
                       value="{{ old('description', $packageExclusion->description) }}"
                       placeholder="e.g. Third-party licence fees">
            </x-admin.field>

            <x-admin.field name="sort_order" label="Sort order" required
                           hint="Lower numbers appear first on the public page.">
                <input type="number" name="sort_order" required min="0"
                       value="{{ old('sort_order', $packageExclusion->sort_order ?? 0) }}">
            </x-admin.field>

            <div class="sm:col-span-2">
                <x-admin.field name="details" label="Details"
                               hint="Optional extra explanation shown under the description.">
                    <textarea name="details" rows="3"
                              placeholder="e.g. Font, hosting and plugin licences are billed separately by the vendor.">{{ old('details', $packageExclusion->details) }}</textarea>
                </x-admin.field>
            </div>

            <div class="sm:col-span-2">
                <label class="admin-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $packageExclusion->active))>
                    Active — show this exclusion on the public packages page
                </label>
            </div>
        </div>
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Save changes
        </button>
        <a href="{{ route('admin.package-exclusions.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection