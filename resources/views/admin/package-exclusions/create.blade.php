@extends('layouts.admin')

@section('title', 'Add Exclusion')
@section('page-title', 'Add Package Exclusion')
@section('page-subtitle', 'Add something that is not included in a standard package')

@section('content')

<form method="POST" action="{{ route('admin.package-exclusions.store') }}" class="max-w-3xl space-y-5">
    @csrf

    <x-admin.note tone="info" title="Write it for the client">
        This text is shown on the public packages page. Say it plainly — the goal is that
        nobody is surprised on delivery day.
    </x-admin.note>

    <x-admin.card title="Exclusion details" icon="shield-off">
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-admin.field name="description" label="Short description" required
                               hint="One line, shown as the headline of the exclusion.">
                    <input type="text" name="description" required maxlength="255"
                           value="{{ old('description') }}"
                           placeholder="e.g. Third-party licence fees">
                </x-admin.field>
            </div>

            <div class="sm:col-span-2">
                <x-admin.field name="details" label="Details"
                               hint="Optional extra explanation shown under the description.">
                    <textarea name="details" rows="3"
                              placeholder="e.g. Font, hosting and plugin licences are billed separately by the vendor.">{{ old('details') }}</textarea>
                </x-admin.field>
            </div>

            <x-admin.field name="sort_order" label="Sort order" required
                           hint="Lower numbers appear first on the public page.">
                <input type="number" name="sort_order" required min="0"
                       value="{{ old('sort_order', 0) }}">
            </x-admin.field>

            <div class="flex items-end pb-2">
                <label class="admin-checkbox">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', true))>
                    Active — show on the public packages page
                </label>
            </div>
        </div>
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="check" class="w-4 h-4" />
            Create exclusion
        </button>
        <a href="{{ route('admin.package-exclusions.index') }}" class="admin-btn-ghost">Cancel</a>
    </div>
</form>

@endsection