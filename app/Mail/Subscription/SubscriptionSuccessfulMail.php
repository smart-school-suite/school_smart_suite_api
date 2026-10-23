<?php

namespace App\Mail\Subscription;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionSuccessfulMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [30, 120, 300, 900];

    public function __construct(
        public readonly string $schoolName,
        public readonly string $planName,
        public readonly string $amount,
        public readonly string $currency,
        public readonly CarbonInterface $startsAt,
        public readonly CarbonInterface $endsAt,
        public readonly ?string $paymentReference = null,
        public readonly ?string $status = 'Active',
        public readonly ?string $recipientName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Smart School Suite subscription is confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription.SubscriptionSuccessful',
            with: [
                'schoolName'       => $this->schoolName,
                'planName'         => $this->planName,
                'amount'           => $this->amount,
                'currency'         => $this->currency,
                'startDate'        => $this->startsAt->format('j F Y'),
                'endDate'          => $this->endsAt->format('j F Y'),
                'paymentReference' => $this->paymentReference,
                'status'           => $this->status,
                'recipientName'    => $this->recipientName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
