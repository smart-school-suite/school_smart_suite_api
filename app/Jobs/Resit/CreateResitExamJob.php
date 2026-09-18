<?php

namespace App\Jobs\Resit;

use App\Models\Exam\Exam;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Course\CourseSpecialty;
use App\Models\Examtype;
use App\Models\ResitExam;
use App\Models\ResitExamRef;
use App\Models\ResitCandidates;
use App\Models\Studentresit;

class CreateResitExamJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public int $backoff = 10;
    protected string $examId;
    protected string $schoolBranchId;

    public function __construct(string $examId, string $schoolBranchId)
    {
        $this->examId = $examId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $exam = Exam::where('school_branch_id', $this->schoolBranchId)
            ->with([
                'schoolYear.specialty',
                'examType.semesters',
            ])
            ->find($this->examId);

        if (!$exam) {
            return;
        }

        $resitExamType = Examtype::where('type', 'resit')
            ->where('semester', $exam->examType->semester)
            ->first();

        if (!$resitExamType) {
            return;
        }

        $examCourseIds = CourseSpecialty::where(
            'school_branch_id',
            $this->schoolBranchId
        )
            ->where(
                'specialty_id',
                $exam->schoolYear->specialty->id
            )
            ->whereHas('course', function ($query) use ($exam) {
                $query->where(
                    'semester_id',
                    $exam->examType->semesters->id
                );
            })
            ->pluck('course_id')
            ->unique()
            ->values();

        $studentResits = Studentresit::where(
            'school_branch_id',
            $this->schoolBranchId
        )
            ->whereIn('course_id', $examCourseIds)
            ->whereNotNull('exam_id')
            ->get();

        if ($studentResits->isEmpty()) {
            return;
        }

        $resitExam = ResitExam::firstOrCreate([
            'school_branch_id' => $this->schoolBranchId,
            'school_year_id' => $exam->school_year_id,
            'exam_type_id' => $resitExamType->id,
        ]);

        $refExamIds = $studentResits
            ->pluck('exam_id')
            ->unique()
            ->values();

        foreach ($refExamIds as $refExamId) {
            ResitExamRef::firstOrCreate([
                'school_branch_id' => $this->schoolBranchId,
                'exam_id' => $refExamId,
                'resit_exam_id' => $resitExam->id,
            ]);
        }

        $studentIds = $studentResits
            ->pluck('student_id')
            ->unique()
            ->values();

        foreach ($studentIds as $studentId) {
            ResitCandidates::firstOrCreate([
                'resit_exam_id' => $resitExam->id,
                'student_id' => $studentId,
                'school_branch_id' => $this->schoolBranchId,
            ]);
        }
    }
}
