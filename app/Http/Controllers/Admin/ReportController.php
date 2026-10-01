<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Scan;
use App\Support\LocalPath;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('scan.website')->latest()->paginate(20);
        return view('admin.reports.index', compact('reports'));
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
