<?php

namespace App\Jobs\DataCreationJob;

use App\Jobs\NotificationJobs\SendAdminResitExamCreatedNotificationJob;
use App\Models\Examtype;
use App\Models\ResitExam;
use App\Models\Exams;
use App\Models\Studentresit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ResitExamRef;
use Illuminate\Support\Str;

class CreateResitExamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The exam instance this job is processing.
     *
     * @var \App\Models\Exams
     */
    protected Exams $exam;

    /**
     * Create a new job instance.
     */
    public function __construct(Exams $exam)
    {
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
            'semester_id' => $this->exam->semester_id
        ])->get();

        if ($studentResits->isNotEmpty() && !$this->resitExamExists()) {
            $this->createResitExam($resitType);
            $this->notifyAdmin($resitType);
        }
    }

    private function getResitType(Examtype $examType): ?Examtype
    {
        return Examtype::where('type', 'resit')
            ->where('semester', $examType->semester)
            ->first();
    }

    private function resitExamExists(): bool
    {
        return ResitExamRef::where('school_branch_id', $this->exam->school_branch_id)
            ->where('semester_id', $this->exam->semester_id)
            ->where("student_batch_id", $this->exam->student_batch_id)
            ->where("specialty_id", $this->exam->specialty_id)
            ->exists();
    }

    private function createResitExam(Examtype $resitType): void
    {
        $resitExamId = Str::uuid()->toString();
        ResitExam::create([
            'id' => $resitExamId,
            'level_id' => $this->exam->level_id,
            'specialty_id' => $this->exam->specialty_id,
            'exam_type_id' => $resitType->id,
            'school_branch_id' => $this->exam->school_branch_id,
            'semester_id' => $this->exam->semester_id,
            'reference_exam_id' => $this->exam->id,
        ]);

        $studentResits = Studentresit::where([
            'school_branch_id' => $this->exam->school_branch_id,
            'specialty_id' => $this->exam->specialty_id,
            'level_id' => $this->exam->level_id,
            'semester_id' => $this->exam->semester_id
        ])
            ->select(
                'level_id',
                'student_batch_id',
                'specialty_id',
                'exam_id',
                'exam_type_id',
                'semester_id'
            )
            ->groupBy(
                'level_id',
                'student_batch_id',
                'specialty_id',
                'exam_id',
                'exam_type_id',
                'semester_id'
            )
            ->get();
        foreach ($studentResits as $studentResit) {
            ResitExamRef::create([
                'school_branch_id' => $studentResit->school_branch_id,
                'exam_type_id' => $studentResit->exam_type_id,
                'level_id' => $studentResit->level_id,
                'exam_id' => $studentResit->exam_id,
                'semester_id' => $studentResit->semester_id,
                'specialty_id' => $studentResit->specialty_id,
                'student_batch_id' => $studentResit->student_batch_id,
                'resit_exam_id' => $resitExamId
            ]);
        }
    }

    private function notifyAdmin(Examtype $resitDetails): void
    {
        $examDetails = Exams::with(['specialty', 'level', 'examtype'])->find($this->exam->id);

        if ($examDetails) {
            SendAdminResitExamCreatedNotificationJob::dispatch($this->exam->school_branch_id, $resitDetails, $examDetails);
        }
    }
}
