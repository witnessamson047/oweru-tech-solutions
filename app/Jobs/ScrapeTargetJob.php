<?php

namespace App\Jobs;

use App\Models\ScrapeTarget;
use App\Services\ScraperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Scrape ONE target on the queue (used by the Watchdog "Check All Now"
 * button). One job per target keeps each unit small, retries independent
 * and the HTTP request that dispatched them instant. The queue worker's
 * 300s timeout comfortably fits a worst-case scrape (~200s including an
 * optional headless-browser render).
 */
class ScrapeTargetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 280;

    public function __construct(
        public ScrapeTarget $target,
    ) {}

    public function handle(): void
    {
        // ScraperClient has required scalar constructor params the container
        // cannot auto-resolve — build it via make() like every other caller.
        $result = ScraperClient::make()->scrape($this->target->url);

        $this->target->update([
            'last_scraped_at' => now(),
            'scrape_count' => $this->target->scrape_count + 1,
            'last_error' => $result['ok'] ? null : $result['message'],
        ]);

        Log::info('ScrapeTargetJob finished', [
            'target_id' => $this->target->id,
            'url' => $this->target->url,
            'ok' => $result['ok'],
            'message' => $result['message'],
        ]);
    }
}
