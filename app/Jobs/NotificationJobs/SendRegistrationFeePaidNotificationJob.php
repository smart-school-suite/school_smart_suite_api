<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\RegistrationFeePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRegistrationFeePaidNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $paymentDetails;

    public function __construct($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->paymentDetails as $feeData){
             $feeData['student']->notify(new RegistrationFeePaid(
                $feeData['amount'],
                now()->format('F j, Y, g:i a')
             ));
        }
    }
}
