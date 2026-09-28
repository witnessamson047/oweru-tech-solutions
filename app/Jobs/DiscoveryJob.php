<?php

namespace App\Jobs;

use App\Models\DiscoveryRun;
use App\Services\DiscoveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Executes one discovery run on the queue worker. The web form creates the
 * DiscoveryRun row immediately (so staff see it as "running") and dispatches
 * this job with it — the worker then talks to Nominatim/Overpass and feeds
 * scrape_targets + discovery_leads.
 *
 * WHY QUEUED (2026-09-25): on this Windows machine, python spawned from the
 * `php artisan serve` process cannot resolve DNS ("[Errno 11003] getaddrinfo
 * failed" for every host, even with injected IP overrides) — a per-process
 * WinSock quirk. The queue worker lineage (started by start.bat, same one
 * that runs every scrape and scan) resolves fine. Queuing also gives the
 * client instant feedback instead of a two-minute frozen form.
 */
class DiscoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 280;

    public function __construct(
        public DiscoveryRun $run,
    ) {}

    public function handle(): void
    {
        // Never let the config timeout exceed this job's own 280s queue
        // timeout — a killed job would leave the run row stuck on 'running'.
        config(['owers.discovery.timeout' => min((int) config('owers.discovery.timeout'), 270)]);

        $run = DiscoveryService::make()->executeRun($this->run);

        Log::info('DiscoveryJob finished', [
            'run_id' => $run->id,
            'city' => $run->city,
            'status' => $run->status,
            'queued' => $run->queued_count,
            'leads' => $run->leads_count,
        ]);
    }

    /**
     * A job that dies before handle() (worker killed, OOM) must not leave the
     * run stuck on 'running' forever — mark it failed so staff can see and retry.
     */
    public function failed(\Throwable $exception): void
    {
        $this->run->update([
            'status' => DiscoveryRun::STATUS_FAILED,
            'error' => 'Discovery job died: ' . $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
