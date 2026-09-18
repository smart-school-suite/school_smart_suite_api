<?php

namespace App\Jobs\Resit;

use App\Models\Course\CourseSpecialty;
use App\Models\Exam\Exam;
use App\Models\Examtype;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\ResitExamRef;
use App\Models\Studentresit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SyncResitExamJob implements ShouldQueue
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

        $resitExam = ResitExam::where('school_branch_id', $this->schoolBranchId)
            ->where('school_year_id', $exam->school_year_id)
            ->where('exam_type_id', $resitExamType->id)
            ->first();

        if (!$resitExam) {
            return;
        }

        DB::transaction(function () use ($exam, $resitExam) {
            $examCourseIds = CourseSpecialty::where('school_branch_id', $this->schoolBranchId)
                ->where('specialty_id', $exam->schoolYear->specialty->id)
                ->whereHas('course', function ($query) use ($exam) {
                    $query->where('semester_id', $exam->examType->semesters->id);
                })
                ->pluck('course_id')
                ->unique();

            $activeResits = Studentresit::where('school_branch_id', $this->schoolBranchId)
                ->whereIn('course_id', $examCourseIds)
                ->whereNotNull('exam_id')
                ->get();

            if ($activeResits->isEmpty()) {
                ResitCandidates::where('resit_exam_id', $resitExam->id)
                    ->where('school_branch_id', $this->schoolBranchId)
                    ->delete();

                ResitExamRef::where('resit_exam_id', $resitExam->id)
                    ->where('school_branch_id', $this->schoolBranchId)
                    ->delete();

                $resitExam->delete();
                return;
            }

            // Sync candidates: Remove candidates who no longer have active resits
            $activeStudentIds = $activeResits->pluck('student_id')->unique();

            ResitCandidates::where('resit_exam_id', $resitExam->id)
                ->where('school_branch_id', $this->schoolBranchId)
                ->whereNotIn('student_id', $activeStudentIds)
                ->delete();

            // Ensure new failed candidates are added
            foreach ($activeStudentIds as $studentId) {
                ResitCandidates::firstOrCreate([
                    'resit_exam_id' => $resitExam->id,
                    'student_id' => $studentId,
                    'school_branch_id' => $this->schoolBranchId,
                ]);
            }

            // Sync referenced exams: Remove exam refs that no longer have failed students
            $activeRefExamIds = $activeResits->pluck('exam_id')->unique();

            ResitExamRef::where('resit_exam_id', $resitExam->id)
                ->where('school_branch_id', $this->schoolBranchId)
                ->whereNotIn('exam_id', $activeRefExamIds)
                ->delete();

            // Ensure active exam refs exist
            foreach ($activeRefExamIds as $refExamId) {
                ResitExamRef::firstOrCreate([
                    'school_branch_id' => $this->schoolBranchId,
                    'exam_id' => $refExamId,
                    'resit_exam_id' => $resitExam->id,
                ]);
            }
        });
    }
}
