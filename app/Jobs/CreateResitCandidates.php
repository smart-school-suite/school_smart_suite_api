<?php

namespace App\Jobs;

use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\Student;
use App\Models\Studentresit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateResitCandidates implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected  $resit;
    public function __construct($resit)
    {
        //
        $this->resit = $resit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        // Logic to create resit candidates
        $resitCandidates = Studentresit::select('student_id', 'specialty_id', 'level_id')
            ->where('specialty_id', $this->resit->specialty_id)
            ->where('level_id', $this->resit->level_id)
            ->distinct()
            ->get();

        foreach ($resitCandidates as $candidate) {
            ResitCandidates::create([
                'student_id' => $candidate->student_id,
                'resit_exam_id' => $this->resit->id,
                'school_branch_id' => $this->resit->school_branch_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update the expected candidate number
        $this->resit->expected_candidate_number = $resitCandidates->count();
        $this->resit->save();
    }
}
