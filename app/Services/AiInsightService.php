<?php

namespace App\Services;

use App\Models\Scan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Component 5 — AI API.
 *
 * The scanner (component 1) produces structured evidence; this service turns
 * that evidence into a plain-language interpretation: an executive summary,
 * prioritised next actions and a ready-to-send pitch email for outreach.
 *
 * Division of responsibility (deliberate):
 *  - WHAT failed and the EVIDENCE comes from the scanner (structured findings).
 *  - The BUSINESS CONSEQUENCE and the OWERU SERVICE to sell come from the
 *    recommendations mapping table — never from AI.
 *  - AI only RE-EXPLAINS the above in plain language for humans.
 *
 * Without an API key (or when the API fails) the service falls back to a
 * deterministic interpretation built from the same structured findings, so
 * the evidence → problems → meaning → services pipeline always completes.
 */
class AiInsightService
{
    public function __construct(
        protected bool $enabled,
        protected ?string $apiKey,
        protected string $baseUrl,
        protected string $model,
        protected int $timeout,
        protected int $maxFindings,
    ) {}

    public static function make(): self
    {
        return new self(
            (bool) config('owers.ai.enabled', true),
            config('owers.ai.api_key'),
            (string) config('owers.ai.base_url', 'https://api.openai.com/v1'),
            (string) config('owers.ai.model', 'gpt-4o-mini'),
            (int) config('owers.ai.timeout', 45),
            (int) config('owers.ai.max_findings', 10),
        );
    }

    /**
     * Interpret the scan's structured findings and persist the insight.
     *
     * @return array|null The stored insight payload, or null when nothing usable.
     */
    public function interpret(Scan $scan): ?array
    {
        $findings = $scan->results()
            ->where('passed', false)
            ->orderBy('points', 'desc') // heaviest losses first
            ->limit($this->maxFindings)
            ->get();

        if ($findings->isEmpty()) {
            return null;
        }

        $evidence = $findings->map(fn ($r) => [
            'check' => $r->check_name,
            'area' => $r->area,
            'evidence' => (string) $r->evidence,
            'impact' => (string) $r->consequence,
            'fix' => (string) ($r->recommendation?->solution ?? ''),
            'service' => (string) ($r->recommendation?->service_type ?? ''),
        ])->all();

        $insight = null;

        if ($this->enabled && $this->apiKey) {
            $insight = $this->viaApi($scan, $evidence);
        }

        // Deterministic fallback: keyless config, disabled, or API failure.
        $insight ??= $this->fallback($scan, $evidence);
        $insight['source'] = array_key_exists('source', $insight) ? $insight['source'] : 'fallback';

        $scan->forceFill(['ai_insight' => $insight])->save();

        Log::info('AI insight generated', [
            'scan_id' => $scan->id,
            'source' => $insight['source'],
        ]);

        return $insight;
    }

    /**
     * Ask the configured chat-completions API for the interpretation.
     * Returns null on any failure (caller falls back deterministically).
     */
    protected function viaApi(Scan $scan, array $evidence): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'temperature' => 0.3,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->userPrompt($scan, $evidence)],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('AI API request failed', [
                    'scan_id' => $scan->id,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $text = $response->json('choices.0.message.content');

            if (!is_string($text) || trim($text) === '') {
                return null;
            }

            $decoded = json_decode($this->stripCodeFence($text), true);

            if (!is_array($decoded) || !isset($decoded['summary'])) {
                Log::warning('AI API returned unparseable insight', ['scan_id' => $scan->id]);
                return null;
            }

            return [
                'source' => 'ai',
                'model' => $this->model,
                'summary' => (string) $decoded['summary'],
                'next_actions' => array_values(array_filter(
                    array_map('strval', (array) ($decoded['next_actions'] ?? []))
                )),
                'pitch_email' => (string) ($decoded['pitch_email'] ?? ''),
                'generated_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            Log::warning('AI API errored', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function systemPrompt(): string
    {
        return <<<'TXT'
You are an analyst for Oweru Tech Solutions, a Tanzanian web agency.
You receive structured website-health findings and explain what they mean for
the BUSINESS, in plain language for a non-technical owner.
Rules:
- Explain only what the evidence shows; never invent problems or metrics.
- Business impact ("impact") and the Oweru service to sell ("service") are
  already decided — do not contradict or replace them.
- Reply with ONLY a JSON object: {"summary": string (2-3 sentences),
  "next_actions": string[] (max 4, ordered most-important first),
  "pitch_email": string (short warm outreach email, <=150 words,
  signed "Oweru Tech Solutions")}. No markdown fences.
TXT;
    }

    protected function userPrompt(Scan $scan, array $evidence): string
    {
        return "Website: {$scan->url}\n"
            . 'Overall score: ' . ($scan->score ?? 'n/a') . "/100 ({$scan->band})\n"
            . "Failed checks (JSON):\n"
            . json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function stripCodeFence(string $text): string
    {
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($text), 2);

        return is_string($trimmed) ? $trimmed : $text;
    }

    /**
     * Deterministic interpretation from the structured findings — the same
     * pipeline with zero external dependencies.
     */
    protected function fallback(Scan $scan, array $evidence): array
    {
        $areas = array_values(array_unique(array_column($evidence, 'area')));
        $highPriority = collect($evidence)
            ->filter(fn ($f) => ($f['service'] ?? '') !== '')
            ->values();

        $summary = count($evidence) . ' health checks failed'
            . ($areas ? ' across ' . implode(', ', $areas) : '')
            . ", putting the site at {$scan->score}/100 ({$scan->band}). "
            . 'Each failing check is a place where visitors may give up on the business.';

        $actions = [];
        foreach ($highPriority->take(4) as $finding) {
            $actions[] = $finding['fix'] !== ''
                ? $finding['fix']
                : 'Fix: ' . $finding['check'];
        }

        $services = $highPriority->pluck('service')->filter()->unique()->take(3)->all();

        $email = "Hello,\n\n"
            . "We ran a free website health check on {$scan->url} and found "
            . count($evidence) . " issues holding the site back (score {$scan->score}/100, {$scan->band}).\n\n"
            . ($actions ? "The priorities we saw:\n- " . implode("\n- ", $actions) . "\n\n" : '')
            . ($services ? 'Our ' . implode(' and ', $services) . " service can fix these for you.\n\n" : '')
            . ("Happy to walk you through the full report — no obligation.\n\n")
            . "Warm regards,\nOweru Tech Solutions";

        return [
            'source' => 'fallback',
            'summary' => $summary,
            'next_actions' => $actions,
            'pitch_email' => $email,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
