<?php

namespace App\Console\Commands\Exam;

use App\Jobs\Exam\CreateExamCandidateJob;
use App\Models\Exams;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateMissingExamCandidates extends Command
{
    protected $signature = 'exams:create-missing-candidates';

    protected $description = 'Dispatch candidate creation for upcoming exams that have no exam candidates yet';

    public function handle(): int
    {
        $dispatched = 0;

        Exams::query()
            ->select(['id', 'school_branch_id'])
            ->whereDate('start_date', '>=', today())
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('exam_candidates')
                    ->whereColumn('exam_candidates.exam_id', 'exams.id')
                    ->whereColumn('exam_candidates.school_branch_id', 'exams.school_branch_id');
            })
            ->chunkById(100, function ($exams) use (&$dispatched) {
                foreach ($exams as $exam) {
                    CreateExamCandidateJob::dispatch($exam->id, $exam->school_branch_id);
                    $dispatched++;
                }
            });

        Log::info('Nightly missing exam candidate check completed', [
            'exams_dispatched' => $dispatched,
        ]);

        $this->info("Dispatched candidate creation for {$dispatched} upcoming exam(s).");

        return self::SUCCESS;
    }
}
