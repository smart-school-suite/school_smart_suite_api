<?php

namespace App\Jobs\AuthenticationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class SendOTPViaEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $otp,
        public readonly int $expiresInMinutes = 10,
        public readonly string $recipientName = 'User'
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(
            new SendOtpMail(
                otp: $this->otp,
                expiresInMinutes: $this->expiresInMinutes,
                recipientName: $this->recipientName
            )
        );
    }
}
