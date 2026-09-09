@extends('layouts.admin')

@section('title', 'Dashboard - Error')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Unable to load dashboard data')

@section('content')
<div class="flex flex-col items-center justify-center p-8 bg-yellow-50 border border-yellow-200 rounded-xl text-center">
    <svg class="w-12 h-12 text-yellow-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
    </svg>
    <h2 class="text-xl font-bold text-gray-900 mb-2">Unable to Load Dashboard</h2>
    <p class="text-gray-600 mb-4 max-w-md">There was a problem connecting to the database. Please check your database configuration.</p>
    <div class="bg-white p-4 rounded-lg border border-gray-200 text-left text-sm text-gray-700 mb-4 max-w-lg">
        <p class="font-medium mb-1">Error Details:</p>
        <p class="text-black font-mono text-xs break-all">{{ $error }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('home') }}" class="btn-outline border-gray-300 text-gray-700 px-4 py-2 text-sm">Go to Homepage</a>
        @if(auth()->check())
            <a href="{{ route('admin.dashboard') }}" class="btn-primary px-4 py-2 text-sm">Retry</a>
        @endif
    </div>
    <div class="mt-6 text-xs text-gray-500">
        <p><strong>Troubleshooting:</strong></p>
        <ul class="mt-2 space-y-1">
            <li>Check that your database server is running</li>
            <li>Verify your .env file has correct DB credentials</li>
            <li>Try: <code class="bg-gray-100 px-1 rounded">php artisan migrate</code></li>
            <li>If using MySQL, ensure port 3306 is accessible</li>
        </ul>
    </div>
</div>
@endsection
