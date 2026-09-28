<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->number} — {$this->invoice->title} ({$this->invoice->deposit_percent}% deposit to start)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'depositUrl' => $this->depositUrl(),
            ],
        );
    }

    public function attachments(): array
    {
        try {
            $pdf = app(\App\Services\InvoiceService::class)->pdf($this->invoice);

            return [
                Attachment::fromData(fn () => $pdf->output(), "invoice-{$this->invoice->number}.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    protected function depositUrl(): string
    {
        return route('payment.checkout', [
            'type' => 'invoice-deposit',
            'id' => $this->invoice->id,
        ]);
    }
}
