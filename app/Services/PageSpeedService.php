<?php

namespace App\Services;

use App\Models\Scan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Component 4 — External API.
 *
 * Fetches real-world mobile performance measurements the HTTP-based scanner
 * cannot take itself (Lighthouse FCP/LCP/TBT/CLS + performance score) from
 * Google PageSpeed Insights and stores them on the scan as structured JSON.
 *
 * Contract: never throws. A PSI outage, bad key or disabled config is logged
 * and simply leaves scan->pagespeed null — a scan must never fail because an
 * external measurement service is down.
 */
class PageSpeedService
{
    public function __construct(
        protected bool $enabled,
        protected ?string $apiKey,
        protected string $baseUrl,
        protected int $timeout,
    ) {}

    public static function make(): self
    {
        return new self(
            (bool) config('owers.external.pagespeed.enabled', true),
            config('owers.external.pagespeed.api_key'),
            (string) config('owers.external.pagespeed.base_url', 'https://www.googleapis.com/pagespeedonline/v5'),
            (int) config('owers.external.pagespeed.timeout', 60),
        );
    }

    /**
     * Measure $scan->url and persist structured metrics onto the scan.
     *
     * @return array|null The stored metrics payload, or null when unavailable.
     */
    public function measure(Scan $scan): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/runPagespeed", [
                    'url' => $scan->url,
                    'strategy' => 'mobile',
                    'category' => 'performance',
                    'locale' => 'en',
                    ...( $this->apiKey ? ['key' => $this->apiKey] : [] ),
                ]);

            if (!$response->successful()) {
                Log::warning('PageSpeed Insights request failed', [
                    'scan_id' => $scan->id,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $payload = $this->extract($response->json());

            if ($payload === null) {
                Log::warning('PageSpeed Insights returned no usable metrics', [
                    'scan_id' => $scan->id,
                ]);
                return null;
            }

            $scan->forceFill(['pagespeed' => $payload])->save();

            Log::info('PageSpeed metrics captured', [
                'scan_id' => $scan->id,
                'psi_score' => $payload['performance_score'] ?? null,
            ]);

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('PageSpeed Insights measurement errored', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Normalise the PSI response into our own stable, machine-readable shape:
     * {performance_score, metrics: {fcp_s, lcp_s, tbt_s, cls}, measured_at}.
     * Field names are ours, so a future PSI API change only touches this one
     * method.
     */
    protected function extract(?array $data): ?array
    {
        $audits = $data['lighthouseResult']['audits'] ?? null;

        if (!is_array($audits)) {
            return null;
        }

        $metrics = [];

        foreach (['first-contentful-paint' => 'fcp_s', 'largest-contentful-paint' => 'lcp_s', 'total-blocking-time' => 'tbt_s', 'cumulative-layout-shift' => 'cls'] as $auditId => $key) {
            $audit = $audits[$auditId] ?? null;
            if ($audit && isset($audit['numericValue']) && is_numeric($audit['numericValue'])) {
                // PSI reports milliseconds for everything except CLS.
                $metrics[$key] = $key === 'cls'
                    ? round((float) $audit['numericValue'], 3)
                    : round((float) $audit['numericValue'] / 1000, 2);
            }
        }

        $score = $data['lighthouseResult']['categories']['performance']['score'] ?? null;
        $performanceScore = is_numeric($score) ? (int) round($score * 100) : null;

        if ($performanceScore === null && $metrics === []) {
            return null;
        }

        return [
            'performance_score' => $performanceScore,
            'metrics' => $metrics,
            'measured_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Plain-language band for a PSI performance score (0-100), used by the
     * dashboard and PDF.
     */
    public static function band(?int $score): string
    {
        if ($score === null) {
            return 'Unknown';
        }
        if ($score >= 90) {
            return 'Good';
        }
        if ($score >= 50) {
            return 'Needs improvement';
        }
        return 'Poor';
    }
}
