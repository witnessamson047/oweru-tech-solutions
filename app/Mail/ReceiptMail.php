<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Receipt — {$this->invoice->title} (Paid in Full)",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
            with: [
                'invoice' => $this->invoice,
            ],
        );
    }

    public function attachments(): array
    {
        try {
            $path = $this->invoice->receipt_path;

            if ($path && file_exists($path)) {
                return [
                    Attachment::fromPath($path)
                        ->as("receipt-{$this->invoice->number}.pdf")
                        ->withMime('application/pdf'),
                ];
            }

            // Fallback: generate fresh
            $receiptPdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', ['invoice' => $this->invoice]);

            return [
                Attachment::fromData(fn () => $receiptPdf->output(), "receipt-{$this->invoice->number}.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
