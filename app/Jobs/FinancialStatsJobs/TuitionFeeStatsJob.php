<?php

namespace App\Jobs\FinancialStatsJobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TuitionFeeStatsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public $tuitionFeePayment;
    public function __construct($tuitionFeePayment)
    {
        $this->tuitionFeePayment = $tuitionFeePayment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
