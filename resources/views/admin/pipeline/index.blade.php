@extends('layouts.admin')

@section('title', 'Pipeline')
@section('page-title', 'Enquiry Pipeline')
@section('page-subtitle', 'Track enquiries through the qualification process')

@section('content')

{{-- Stage Filters --}}
<div class="flex flex-wrap gap-2 mb-6">
    <button class="pipeline-filter px-4 py-2 rounded-lg text-sm font-medium bg-yellow-500 text-black" data-stage="all">All</button>
    @foreach(['new', 'qualified', 'diagnostic_paid', 'proposal_sent', 'won', 'lost'] as $stage)
        <button class="pipeline-filter px-4 py-2 rounded-lg text-sm font-medium stage-{{ $stage === 'diagnostic_paid' ? 'diagnostic' : $stage }}" data-stage="{{ $stage }}">
            {{ str_replace('_', ' ', ucfirst($stage)) }}
            <span class="ml-1 opacity-75">({{ $pipeline[$stage]->count() ?? 0 }})</span>
        </button>
    @endforeach
</div>

{{-- Pipeline Board --}}
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
    @foreach(['new', 'qualified', 'diagnostic_paid', 'proposal_sent', 'won', 'lost'] as $stage)
        <div class="bg-gray-50 rounded-xl p-3 min-h-[300px]">
            <div class="flex items-center gap-2 mb-3">
                <span class="badge stage-{{ $stage === 'diagnostic_paid' ? 'diagnostic' : $stage }}">
                    {{ str_replace('_', ' ', ucfirst($stage)) }}
                </span>
                <span class="text-xs text-gray-400">{{ $pipeline[$stage]->count() ?? 0 }}</span>
            </div>
            <div class="space-y-2">
                @forelse($pipeline[$stage] ?? [] as $enquiry)
                    <a href="{{ route('admin.enquiries.show', $enquiry) }}"
                        class="pipeline-row block bg-white rounded-lg p-3 shadow-sm hover:shadow-md transition border border-gray-100"
                        data-stage="{{ $stage }}">
                        <div class="font-medium text-sm text-gray-900 truncate">{{ $enquiry->business_name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5 truncate">{{ $enquiry->name }}</div>
                        <div class="flex items-center justify-between mt-2">
                            @if($enquiry->package_name)
                                <span class="text-[10px] bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full truncate max-w-[120px]">{{ $enquiry->package_name }}</span>
                            @else
                                <span></span>
                            @endif
                            <span class="text-[10px] text-gray-400">{{ $enquiry->created_at->diffForHumans() }}</span>
                        </div>
                        @if($enquiry->owner)
                            <div class="mt-2 flex items-center gap-1">
                                <div class="w-4 h-4 bg-gray-300 rounded-full flex items-center justify-center text-[8px] text-gray-600 font-bold">
                                    {{ substr($enquiry->owner->name, 0, 1) }}
                                </div>
                                <span class="text-[10px] text-gray-500">{{ $enquiry->owner->name }}</span>
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="text-center text-xs text-gray-400 py-4">No enquiries</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@endsection
