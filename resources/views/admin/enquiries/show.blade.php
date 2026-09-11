@extends('layouts.admin')

@section('title', 'Enquiry Details')
@section('page-title', 'Enquiry: ' . $enquiry->name)
@section('page-subtitle', $enquiry->business_name)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Details --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Contact Info --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Name:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Business:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->business_name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Email:</span>
                    <a href="mailto:{{ $enquiry->email }}" class="font-medium text-yellow-600 ml-2">{{ $enquiry->email }}</a>
                </div>
                <div>
                    <span class="text-gray-500">Phone:</span>
                    <a href="tel:{{ $enquiry->phone }}" class="font-medium text-yellow-600 ml-2">{{ $enquiry->phone }}</a>
                </div>
                <div>
                    <span class="text-gray-500">Country:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->country }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Source:</span>
                    <span class="badge badge-info">{{ ucfirst($enquiry->source ?? 'direct') }}</span>
                </div>
            </div>
        </div>

        {{-- Project Details --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Project Details</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500">Package Interest:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->package_name ?? 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Budget Range:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ str_replace('_', ' ', ucfirst($enquiry->budget_range ?? '')) }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Required Date:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->required_date ? \Carbon\Carbon::parse($enquiry->required_date)->format('d M Y') : 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Current Cost/Impact:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $enquiry->current_cost ?: 'Not specified' }}</span>
                </div>
            </div>
        </div>

        {{-- Problem Description --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Problem Description</h3>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $enquiry->problem_description }}</p>
        </div>

        {{-- Notes --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Internal Notes</h3>
            <form method="POST" action="{{ route('admin.enquiries.notes', $enquiry) }}">
                @csrf
                <textarea name="notes" rows="3"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500"
                    placeholder="Add internal notes...">{{ $enquiry->notes }}</textarea>
                <button type="submit" class="btn-primary text-sm mt-2">Save Notes</button>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Stage Management --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Pipeline Stage</h3>
            <div class="mb-4">
                <span class="badge stage-{{ $enquiry->stage === 'diagnostic_paid' ? 'diagnostic' : $enquiry->stage }} text-base px-4 py-1.5">
                    {{ str_replace('_', ' ', ucfirst($enquiry->stage)) }}
                </span>
            </div>
            <form method="POST" action="{{ route('admin.enquiries.stage', $enquiry) }}">
                @csrf
                @method('PATCH')
                <div class="space-y-2">
                    @foreach(['new', 'qualified', 'diagnostic_paid', 'proposal_sent', 'won', 'lost'] as $stage)
                        <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="stage" value="{{ $stage }}" {{ $enquiry->stage === $stage ? 'checked' : '' }}
                                class="text-yellow-600 focus:ring-yellow-500">
                            <span class="text-sm text-gray-700">{{ str_replace('_', ' ', ucfirst($stage)) }}</span>
                            @if($enquiry->stage === $stage)
                                <span class="ml-auto text-xs text-yellow-600">Current</span>
                            @endif
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="btn-primary text-sm w-full justify-center mt-3">Update Stage</button>
            </form>
        </div>

        {{-- Owner --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Assigned Owner</h3>
            <form method="POST" action="{{ route('admin.enquiries.owner', $enquiry) }}">
                @csrf
                @method('PATCH')
                <select name="owner_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Unassigned</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->id }}" {{ $enquiry->owner_id === $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-outline text-sm w-full justify-center mt-2">Assign</button>
            </form>
        </div>

        {{-- Timestamps --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Timeline</h3>
            <div class="space-y-2 text-xs text-gray-500">
                <div class="flex justify-between">
                    <span>Created</span>
                    <span>{{ $enquiry->created_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Last Updated</span>
                    <span>{{ $enquiry->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Stage Changed</span>
                    <span>{{ $enquiry->last_stage_changed_at ? \Carbon\Carbon::parse($enquiry->last_stage_changed_at)->diffForHumans() : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Actions</h3>
            <div class="space-y-2">
                <a href="mailto:{{ $enquiry->email }}" class="btn-outline text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Send Email
                </a>
                <a href="tel:{{ $enquiry->phone }}" class="btn-outline text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Call
                </a>
                @if($enquiry->source === 'scanner' && $enquiry->scan)
                    <a href="{{ route('admin.scans.show', $enquiry->scan) }}" class="btn-outline text-sm w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        View Scan Results
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
