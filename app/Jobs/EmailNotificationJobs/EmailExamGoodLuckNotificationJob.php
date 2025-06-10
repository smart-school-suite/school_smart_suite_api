<?php

namespace App\Jobs\EmailNotificationJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ExamGoodLuckWishEmailNotification;
use illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use illuminate\Support\Facades\Log;

class EmailExamGoodLuckNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipientEmail;
    protected $recipientName;
    protected $examTitle;
    protected $examDateTime;

    /**
     * Create a new job instance.
     */
    public function __construct($recipientEmail, $recipientName, $examTitle, Carbon $examDateTime, $recipientType)
    {
        $this->recipientEmail = $recipientEmail;
        $this->recipientName = $recipientName;
        $this->examTitle = $examTitle;
        $this->examDateTime = $examDateTime;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->recipientEmail)->send(new ExamGoodLuckWishEmailNotification($this->recipientName, $this->examTitle, $this->examDateTime));
            Log::info("Good luck exam wish sent to {$this->recipientName} ({$this->recipientEmail}) for exam '{$this->examTitle}'.");
        } catch (\Exception $e) {
            Log::error("Failed to send good luck exam wish to {$this->recipientName} ({$this->recipientEmail}) for exam '{$this->examTitle}': " . $e->getMessage());
        }
    }
}
