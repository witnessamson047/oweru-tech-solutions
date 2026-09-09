<?php

use Illuminate\Support\Facades\Schedule;

// Pipeline hygiene: remind staff about enquiries stuck in one stage
Schedule::command('enquiries:send-stage-reminders --days=3')->weekdays()->dailyAt('09:00');
