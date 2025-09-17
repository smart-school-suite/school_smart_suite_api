<?php

namespace App\Jobs\NotificationJobs;

use App\Notifications\AdditionalFee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAdditionalFeeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    protected $additionalFeeData;
    public function __construct(array $additionalFeeData)
    {
        $this->additionalFeeData = $additionalFeeData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach($this->additionalFeeData as $fee){
            $fee['student']->notify( new AdditionalFee($fee['amount'], $fee['reason']));
        }
    }
}
