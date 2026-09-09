@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Generated one-page PDF reports')

@section('content')

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Business</th>
                    <th>Website</th>
                    <th>Score</th>
                    <th>Band</th>
                    <th>Generated</th>
                    <th>Downloaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td class="font-medium text-sm text-gray-900">{{ $report->scan->website->business_name ?? '-' }}</td>
                        <td class="text-sm text-gray-600 max-w-[200px] truncate">{{ $report->scan->url ?? '-' }}</td>
                        <td>
                            <span class="badge {{ ($report->scan->score ?? 0) >= 80 ? 'badge-success' : (($report->scan->score ?? 0) >= 60 ? 'badge-info' : (($report->scan->score ?? 0) >= 40 ? 'badge-warning' : 'badge-danger')) }}">
                                {{ $report->scan->score ?? '-' }}/100
                            </span>
                        </td>
                        <td class="text-sm text-gray-700">{{ $report->scan->band ?? '-' }}</td>
                        <td class="text-xs text-gray-500">{{ $report->generated_at?->diffForHumans() ?? '-' }}</td>
                        <td class="text-sm text-gray-700">{{ $report->downloads ?? 0 }}</td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.reports.download', $report) }}" class="text-yellow-600 hover:underline text-sm">Download</a>
                                <a href="{{ route('admin.scans.show', $report->scan) }}" class="text-gray-600 hover:underline text-sm">View Scan</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">No reports generated yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($reports, 'links'))
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $reports->links() }}
        </div>
    @endif
</div>

@endsection
