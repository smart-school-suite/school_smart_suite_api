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

class UpdateExamStatusJob implements ShouldQueue
{
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;

    public function handle(): void
    {
        $today = Carbon::today();

        $updated = [
            'finished' => DB::table('exams')
                ->where('end_date', '<', $today)
                ->where('status', '!=', 'finished')
                ->update(['status' => 'finished']),

            'progressing' => DB::table('exams')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->where('status', '!=', 'inprogress')
                ->update(['status' => 'inprogress']),

            'pending' => DB::table('exams')
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
