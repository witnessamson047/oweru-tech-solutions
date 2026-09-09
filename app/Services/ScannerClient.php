<?php

namespace App\Services;

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
     * Trigger a scan on the Python scanner service and persist the results.
     *
     * @return array{ok: bool, message: string, scan: Scan}
     */
    public function runScan(Scan $scan): array
    {
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
