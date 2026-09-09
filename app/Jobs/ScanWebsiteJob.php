<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Website;
use App\Services\ScannerClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public Website $website,
        public string $source = 'admin_batch',
    ) {}

    public function handle(): void
    {
        $scan = Scan::create([
            'website_id' => $this->website->id,
            'url' => $this->website->url,
            'status' => 'running',
            'source' => $this->source,
        ]);

        $result = ScannerClient::make()->runScan($scan);

        Log::info('Queued scan finished', [
            'scan_id' => $scan->id,
            'website' => $this->website->url,
            'ok' => $result['ok'],
            'message' => $result['message'],
        ]);
    }
}
