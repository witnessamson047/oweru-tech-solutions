<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        $days = $this->invoice->days_overdue;

        return new Envelope(
            subject: "Reminder: deposit overdue for invoice {$this->invoice->number}"
                . ($days > 0 ? " ({$days} day" . ($days === 1 ? '' : 's') . " past due)" : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deposit-reminder',
            with: [
                'invoice' => $this->invoice,
                'depositUrl' => route('payment.checkout', [
                    'type' => 'invoice-deposit',
                    'id' => $this->invoice->id,
                ]),
            ],
        );
    }
}
