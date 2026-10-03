<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\Report;
use App\Models\Scan;
use App\Support\LocalPath;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'generated_at' => 'Generated',
            'downloads' => 'Downloads',
            'created_at' => 'Newest',
        ], default: 'created_at', defaultDirection: 'desc');

        $query = Report::with('scan.website');

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('scan.website', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%");
            });
        }

        $this->applySort($query, $sort);

        $reports = $query->paginate($this->perPage($request, 20))->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'sort' => $sort,
            'stats' => [
                'total' => Report::count(),
                'downloads' => (int) Report::sum('downloads'),
                'this_month' => Report::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
            ],
        ]);
    }

    public function generate(Scan $scan)
    {
        $scan->load(['website', 'results.check', 'results.recommendation']);

        // Get top 5 lowest-scoring (failed) findings, with recommendation mappings
        $findings = $scan->results
            ->where('passed', false)
            ->sortBy(fn ($r) => [$r->recommendation?->priority === 'high' ? 0 : 1, $r->points])
            ->take(5)
            ->values();

        // Generate PDF
        $pdf = Pdf::loadView('reports.scan-report', [
            'scan' => $scan,
            'findings' => $findings,
        ]);

        $fileName = "oweru-report-{$scan->id}-" . now()->format('Y-m-d') . '.pdf';
        $filePath = LocalPath::reportsDir() . "/{$fileName}";

        // Ensure directory exists
        if (! is_dir(LocalPath::reportsDir())) {
            mkdir(LocalPath::reportsDir(), 0755, true);
        }

        $pdf->save($filePath);

        // Create or update report record
        Report::updateOrCreate(
            ['scan_id' => $scan->id],
            [
                'file_path' => $filePath,
                'generated_at' => now(),
            ]
        );

        return redirect()->route('admin.scans.show', $scan)
            ->with('success', 'Report generated successfully.');
    }

    public function download(Report $report)
    {
        $report->increment('downloads');

        if (!file_exists($report->file_path)) {
            return redirect()->back()->with('error', 'Report file not found.');
        }

        return response()->download($report->file_path);
    }
}
