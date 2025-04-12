<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Mail\EventMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventEmail implements ShouldQueue
{
    use Dispatchable,  Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $description;
    protected $recipients;
    public function __construct($recipients, $description)
    {
        //
        $this->description = $description;
        $this->recipients = $recipients;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        foreach ($this->recipients as $recipient) {
            Mail::to($recipient)->send(new EventMail( $this->description));
        }
    }
}
