<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendStageReminders extends Command
{
    protected $signature = 'enquiries:send-stage-reminders {--days=3 : Working days before reminding}';

    protected $description = 'Send reminders for enquiries stuck in one pipeline stage for N or more working days';

    public function handle(NotificationService $notifications): int
    {
        $days = (int) $this->option('days');

        $count = $notifications->stageReminders($days);

        $this->info("Stage reminders recorded/sent for {$count} stale enquiry(ies).");

        return self::SUCCESS;
    }
}
