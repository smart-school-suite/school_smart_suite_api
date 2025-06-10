<?php

namespace App\Jobs\EmailNotificationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use illuminate\Support\Facades\Log;
use App\Mail\BirthdayWishEmailNotification;
use Illuminate\Support\Facades\Mail;
class EmailBirthDayNotificationJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipientEmail;
    protected $recipientName;
    protected $recipientType;

    /**
     * Create a new job instance.
     */
    public function __construct($recipientEmail, $recipientName, $recipientType)
    {
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send(new BirthdayWishEmailNotification($this->recipientName, $this->recipientType));
            Log::info("Birthday wish sent to {$this->recipientName} ({$this->recipientEmail}) as a {$this->recipientType}.");
        } catch (\Exception $e) {
            Log::error("Failed to send birthday wish to {$this->recipientName} ({$this->recipientEmail}): " . $e->getMessage());
        }
    }
}
