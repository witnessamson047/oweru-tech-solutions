<?php

namespace App\Mail;

use App\Models\Scan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScanResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Scan $scan,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Website Health Report — {$this->scan->score}/100 ({$this->scan->band})",
        );
    }

    public function content(): Content
    {
        $findings = $this->scan->results
            ->where('passed', false)
            ->sortBy('points')
            ->take(5)
            ->values();

        return new Content(
            view: 'emails.scan-results',
            with: [
                'scan' => $this->scan,
                'findings' => $findings,
                'score' => $this->scan->score,
                'band' => $this->scan->band,
                'url' => $this->scan->url,
            ],
        );
    }
}
