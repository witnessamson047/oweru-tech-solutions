<?php

namespace App\Services;

use App\Jobs\PostScanJob;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\ScannerCheck;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScannerClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
        protected int $timeout = 120,
    ) {}

    public static function make(): self
    {
        return new self(
            config('services.scanner.url', 'http://localhost:5000'),
            (string) config('services.scanner.api_key', ''),
        );
    }

    /**
     * Get the admin-configurable check configuration sent to the Python
     * scanner with every scan request. Weights live in the scanner_checks
     * table (editable at /admin/scanner-checks); disabled checks are omitted
     * so the scanner never runs them.
     *
     * @return array<int, array{name: string, weight: int, enabled: bool}>
     */
    public static function checksPayload(): array
    {
        return ScannerCheck::query()
            ->orderBy('id')
            ->get(['name', 'weight', 'enabled'])
            ->map(fn (ScannerCheck $check) => [
                'check_name' => $check->name,
                'weight' => (int) $check->weight,
                'enabled' => (bool) $check->enabled,
            ])
        ->all();
    }

    /**
     * Is the Python scanner engine reachable right now?
     * Used as a fast pre-flight before a scan so staff get an actionable
     * message ("start the engine") instead of a bare connection error.
     */
    public function engineOnline(): bool
    {
        try {
            return Http::timeout(4)
                ->withHeaders(['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'])
                ->get("{$this->baseUrl}/health")
                ->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Trigger a scan on the Python scanner service and persist the results.
     *
     * @return array{ok: bool, message: string, scan: Scan}
     */
    public function runScan(Scan $scan): array
    {
        // Pre-flight: fail fast with a helpful hint when the engine is down.
        // (Skipped under testing: keep tests hermetic — no real network calls.)
        if (! app()->environment('testing') && ! $this->engineOnline()) {
            $scan->update([
                'status' => 'failed',
                'error_message' => 'Scanner engine offline (no response from ' . $this->baseUrl . ')',
                'completed_at' => now(),
            ]);

            return [
                'ok' => false,
                'message' => 'Scanner engine is OFFLINE — start it with start.bat (or C:\\python312\\python.exe scanner\\scanner.py), then run the scan again.',
                'scan' => $scan,
            ];
        }

        $scan->update([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
        ]);
        $scan->results()->delete();

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/scanner/scan", [
                    'scan_id' => $scan->id,
                    'url' => $scan->url,
                    'checks' => self::checksPayload(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Scanner service unreachable', ['scan_id' => $scan->id, 'error' => $e->getMessage()]);

            $scan->update([
                'status' => 'failed',
                'error_message' => 'Scanner service unreachable: ' . $e->getMessage(),
                'completed_at' => now(),
            ]);

            return ['ok' => false, 'message' => 'Scanner service is currently unavailable.', 'scan' => $scan];
        }

        $data = $response->json();

        if (!$response->successful() || empty($data['success'])) {
            $message = $data['error'] ?? $data['message'] ?? 'Scan failed.';

            $scan->update([
                'status' => 'failed',
                'error_message' => $message,
                'completed_at' => now(),
            ]);

            return ['ok' => false, 'message' => $message, 'scan' => $scan];
        }

        $score = (int) ($data['score'] ?? 0);

        $scan->update([
            'status' => 'completed',
            'score' => $score,
            'band' => Scan::calculateBand($score),
            'completed_at' => now(),
        ]);

        foreach ($data['results'] ?? [] as $result) {
            $this->storeResult($scan, $result);
        }

        // Dispatch post-scan automation (PDF generation, email, notifications)
        PostScanJob::dispatch($scan);

        return ['ok' => true, 'message' => "Scan completed with score {$score}.", 'scan' => $scan];
    }

    /**
     * Store a single check result, linking it to the scanner_checks row by name when possible.
     */
    public function storeResult(Scan $scan, array $result): ScanResult
    {
        $checkId = ScannerCheck::where('name', $result['check_name'] ?? '')->value('id');

        return ScanResult::create([
            'scan_id' => $scan->id,
            'check_id' => $checkId,
            'check_name' => $result['check_name'] ?? 'Unknown check',
            'area' => $result['area'] ?? 'Other',
            'passed' => (bool) ($result['passed'] ?? false),
            'points' => (int) ($result['points'] ?? 0),
            'evidence' => $result['evidence'] ?? null,
            'finding_text' => $result['finding_text'] ?? null,
            'consequence' => $result['consequence'] ?? null,
        ]);
    }

    /**
     * Store a full result set (used by the async callback endpoint).
     */
    public function storeResults(Scan $scan, array $results, ?int $score = null): void
    {
        $scan->results()->delete();

        foreach ($results as $result) {
            $this->storeResult($scan, $result);
        }

        if ($score !== null) {
            $score = (int) $score;
            $scan->update([
                'score' => $score,
                'band' => Scan::calculateBand($score),
            ]);
        }
    }
}
