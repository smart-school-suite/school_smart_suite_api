<?php

namespace App\Jobs\DataCreationJob;

use App\Models\ResitCandidates;
use App\Models\Studentresit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateResitCandidateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $tries = 3;
    protected  $resitExam;
    public function __construct($resitExam)
    {
        //
        $this->resitExam = $resitExam;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        // Logic to create resit candidates
        $resitExamCandidates = Studentresit::select('student_id', 'specialty_id', 'level_id')
            ->where('specialty_id', $this->resitExam->specialty_id)
            ->where('level_id', $this->resitExam->level_id)
            ->where("semester_id", $this->resitExam->semester_id)
            ->distinct()
            ->get();

        foreach ($resitExamCandidates as $candidate) {
            ResitCandidates::create([
                'student_id' => $candidate->student_id,
                'resit_exam_id' => $this->resitExam->id,
                'school_branch_id' => $this->resitExam->school_branch_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update the expected candidate number
        $this->resitExam->expected_candidate_number = $resitExamCandidates->count();
        $this->resitExam->save();
    }
}
