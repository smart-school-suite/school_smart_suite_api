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
use Illuminate\Support\Facades\Log;

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
            // Log an error or handle the case where the resit type doesn't exist.
            Log::warning('Resit exam type not found for semester: ' . $examType->semester);
            return;
        }

        $studentResits = Studentresit::where([
            'school_branch_id' => $this->exam->school_branch_id,
            'specialty_id' => $this->exam->specialty_id,
            'level_id' => $this->exam->level_id,
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
        return ResitExam::where('school_branch_id', $this->exam->school_branch_id)
            ->where('reference_exam_id', $this->exam->id)
            ->exists();
    }

    private function createResitExam(Examtype $resitType): void
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

    private function notifyAdmin(Examtype $resitDetails): void
    {
        $examDetails = Exams::with(['specialty', 'level', 'examtype'])->find($this->exam->id);

        if ($examDetails) {
            Log::info("Exam details: " . json_encode($examDetails->toArray()));
            Log::info("Resit details: " . json_encode($resitDetails->toArray()));

            SendAdminResitExamCreatedNotificationJob::dispatch($this->exam->school_branch_id, $resitDetails, $examDetails);
        }
    }
}
