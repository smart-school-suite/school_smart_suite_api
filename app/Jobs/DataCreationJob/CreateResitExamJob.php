<?php

namespace App\Jobs\DataCreationJob;

use App\Models\Examtype;
use App\Models\ResitExam;
use App\Models\Exams;
use App\Models\Studentresit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateResitExamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $exam;
    public function __construct(Exams $exam)
    {
        //
        $this->exam = $exam;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $examType = $this->exam->examType;

        if ($examType->type !== 'exam') {
            return;
        }

        $resitType = $this->getResitType($examType);
        if (!$resitType) {
            return;
        }

        $studentResits = Studentresit::where([
            'school_branch_id' => $this->exam->school_branch_id,
            'specialty_id' => $this->exam->specialty_id,
            'level_id' => $this->exam->level_id,
        ])->get();

        if ($studentResits->isNotEmpty() && !$this->resitExamExists()) {
            $this->createResitExam($resitType);
        }
    }

    private function getResitType($examType)
    {
        return Examtype::where('type', 'resit')
            ->where('semester', $examType->semester)
            ->first();
    }

    private function resitExamExists()
    {
        return ResitExam::where('school_branch_id', $this->exam->school_branch_id)
            ->where('reference_exam_id', $this->exam->id)
            ->exists();
    }

    private function createResitExam($resitType)
    {
        ResitExam::create([
            'level_id' => $this->exam->level_id,
            'specialty_id' => $this->exam->specialty_id,
            'exam_type_id' => $resitType->id,
            'school_branch_id' => $this->exam->school_branch_id,
            'semester_id' => $this->exam->semester_id,
            'reference_exam_id' => $this->exam->id,
        ]);
    }
}
