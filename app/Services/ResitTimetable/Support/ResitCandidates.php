<?php

namespace App\Services\ResitTimetable\Support;

use App\Models\ResitExam;
use App\Models\ResitExamRef;
use App\Models\Studentresit;
use Illuminate\Support\Collection;

/**
 * Who resits what in a resit exam: the students who failed a course in one of
 * the exams the resit exam refers to (resit_exam_references), for the resit
 * exam's own specialty year.
 */
class ResitCandidates
{
    /** @return array<string, string[]> course_id => student ids */
    public function byCourse(string $schoolBranchId, ResitExam $resitExam): array
    {
        return $this->rows($schoolBranchId, $resitExam)
            ->groupBy('course_id')
            ->map(fn(Collection $rows) => $rows->pluck('student_id')->map(fn($id) => (string) $id)->unique()->values()->all())
            ->all();
    }

    /** @return Collection<Studentresit> */
    public function rows(string $schoolBranchId, ResitExam $resitExam): Collection
    {
        $examIds = ResitExamRef::where('school_branch_id', $schoolBranchId)
            ->where('resit_exam_id', $resitExam->id)
            ->pluck('exam_id')
            ->unique()
            ->values();

        if ($examIds->isEmpty()) {
            return collect();
        }

        return Studentresit::where('school_branch_id', $schoolBranchId)
            ->whereIn('exam_id', $examIds)
            ->when($resitExam->school_year_id, fn($query) => $query->whereHas(
                'exam',
                fn($exam) => $exam->where('school_year_id', $resitExam->school_year_id)
            ))
            ->get(['id', 'student_id', 'course_id', 'exam_id', 'payment_status']);
    }
}
