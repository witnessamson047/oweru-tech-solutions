<?php

namespace App\Jobs;

use App\Mail\ScanResultsMail;
use App\Models\Report;
use App\Models\Scan;
use App\Services\AiInsightService;
use App\Services\NotificationService;
use App\Services\PageSpeedService;
use App\Support\LocalPath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PostScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public Scan $scan,
    ) {}

    public function handle(): void
    {
        $this->scan->load(['website', 'results.check', 'results.recommendation']);

        // 0. Enrich evidence: real-world performance (component 4) then AI
        //    interpretation (component 5). Both are log-only — a PSI or AI
        //    outage can never fail the scan pipeline.
        $this->capturePageSpeed();
        $this->interpretWithAi();

        // 1. Auto-generate PDF report
        $this->generatePdf();

        // 2. Send email results to customer (if we have their email)
        $this->sendEmailResults();

        // 3. Notify staff about completed scan
        $this->notifyStaff();

        Log::info('PostScanJob completed', [
            'scan_id' => $this->scan->id,
            'score' => $this->scan->score,
        ]);
    }

    protected function capturePageSpeed(): void
    {
        try {
            PageSpeedService::make()->measure($this->scan);
        } catch (\Throwable $e) {
            Log::warning('PageSpeed enrichment failed', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function interpretWithAi(): void
    {
        try {
            AiInsightService::make()->interpret($this->scan);
        } catch (\Throwable $e) {
            Log::warning('AI interpretation failed', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function generatePdf(): void
    {
        try {
            // Skip if report already exists
            if ($this->scan->report) {
                return;
            }

            $findings = $this->scan->results
                ->where('passed', false)
                ->sortBy(fn ($r) => [$r->recommendation?->priority === 'high' ? 0 : 1, $r->points])
                ->take(5)
                ->values();

            $pdf = Pdf::loadView('reports.scan-report', [
                'scan' => $this->scan,
                'findings' => $findings,
            ]);

            $fileName = "oweru-report-{$this->scan->id}-" . now()->format('Y-m-d') . '.pdf';
            $filePath = LocalPath::reportsDir() . "/{$fileName}";

            if (! is_dir(LocalPath::reportsDir())) {
                mkdir(LocalPath::reportsDir(), 0755, true);
            }

            $pdf->save($filePath);

            Report::updateOrCreate(
                ['scan_id' => $this->scan->id],
                [
                    'file_path' => $filePath,
                    'generated_at' => now(),
                ]
            );

            Log::info('PDF report auto-generated', ['scan_id' => $this->scan->id, 'path' => $filePath]);
        } catch (\Throwable $e) {
            Log::warning('Failed to auto-generate PDF', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendEmailResults(): void
    {
        try {
            // Get email from linked enquiry
            $email = null;

            if ($this->scan->enquiry && $this->scan->enquiry->email) {
                $email = $this->scan->enquiry->email;
            } elseif ($this->scan->website && $this->scan->website->enquiry && $this->scan->website->enquiry->email) {
                $email = $this->scan->website->enquiry->email;
            }

            // Also check payment metadata for email
            if (!$email) {
                $payment = $this->scan->payment;
                if ($payment && $payment->metadata && isset($payment->metadata['email'])) {
                    $email = $payment->metadata['email'];
                }
            }

            if (!$email) {
                Log::info('No email found for scan results', ['scan_id' => $this->scan->id]);
                return;
            }

            Mail::to($email)->send(new ScanResultsMail($this->scan));

            Log::info('Scan results email sent', ['scan_id' => $this->scan->id, 'email' => $email]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send scan results email', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function notifyStaff(): void
    {
        try {
            if ($this->scan->enquiry) {
                // If there's a linked enquiry, use the existing notification service
                app(NotificationService::class)->newEnquiry($this->scan->enquiry);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to notify staff about scan', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
