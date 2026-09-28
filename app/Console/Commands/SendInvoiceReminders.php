<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders';

    protected $description = 'Email customers whose invoice deposit is overdue (every N days, max M reminders)';

    public function handle(InvoiceService $invoices): int
    {
        $due = $invoices->overdueInvoices()->count();

        if ($due === 0) {
            $this->info('No invoices are due an overdue-deposit reminder.');

            return self::SUCCESS;
        }

        $sent = $invoices->sendOverdueReminders();

        $this->info("Overdue-deposit reminders sent for {$sent} of {$due} due invoice(s).");

        return self::SUCCESS;
    }
}
