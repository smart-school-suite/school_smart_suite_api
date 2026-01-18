<?php

namespace App\Jobs\UpdateStatus;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateSemesterStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function handle(): void
    {
        $today = Carbon::today();

        $updated = [
            'expired' => DB::table('school_semesters')
                ->where('end_date', '<', $today)
                ->where('status', '!=', 'expired')
                ->update(['status' => 'expired']),

            'activated' => DB::table('school_semesters')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->where('status', '!=', 'active')
                ->update(['status' => 'active']),

            'pending' => DB::table('school_semesters')
                ->where('start_date', '>', $today)
                ->where('status', '!=', 'pending')
                ->update(['status' => 'pending']),
        ];

        Log::info('Semester statuses updated', [
            'date'     => $today->toDateString(),
            'changes'  => $updated,
            'job_id'   => $this->job?->getJobId(),
        ]);
    }
}
