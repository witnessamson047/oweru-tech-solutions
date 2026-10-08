@extends('layouts.admin')

@section('title', $title ?? 'Error')
@section('page-title', $pageTitle ?? 'Error')
@section('page-subtitle', 'Unable to load data')

@section('content')

<div class="mx-auto max-w-2xl space-y-5">
    <x-admin.card :padded="true">
        <div class="flex flex-col items-center text-center">
            <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full" style="background-color: var(--admin-warn-soft); color: var(--admin-warn)">
                <x-admin.icon name="warning" class="h-7 w-7" />
            </span>
            <h2 class="text-xl font-bold text-gray-900">{{ $heading ?? 'Unable to load this page' }}</h2>
            <p class="mt-1 max-w-md text-sm text-gray-600">
                There was a problem connecting to the database. Check your database configuration
                and try again.
            </p>

            <div class="mt-4 w-full rounded-lg border text-left" style="border-color: var(--admin-border); background-color: var(--gray-light)">
                <p class="admin-label px-4 pt-3">Error details</p>
                <pre class="overflow-x-auto whitespace-pre-wrap px-4 pb-3 font-mono text-xs text-gray-800">{{ $error }}</pre>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <x-admin.action :href="route('home')" icon="external">Go to homepage</x-admin.action>
                @if (auth()->check())
                    <x-admin.action :href="url()->current()" variant="primary" icon="refresh">Retry</x-admin.action>
                @endif
            </div>
        </div>
    </x-admin.card>

    <x-admin.note tone="warn" title="Troubleshooting">
        <ul class="list-inside list-disc space-y-1">
            <li>Check that your database server is running.</li>
            <li>Verify your <code class="rounded bg-gray-100 px-1">.env</code> file has the correct DB credentials.</li>
            <li>Run <code class="rounded bg-gray-100 px-1">php artisan migrate</code> if the schema is missing.</li>
            <li>If using MySQL, ensure port 3306 is accessible.</li>
        </ul>
    </x-admin.note>
</div>

@endsection
