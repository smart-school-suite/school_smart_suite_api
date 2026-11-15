<?php

namespace App\Services\ResitJointCourse;

use App\Models\ResitExam;
use App\Models\ResitJointCourse\ResitJointCourse;
use App\Models\ResitJointCourse\ResitJointCourseRef;
use App\Models\ResitJointCourse\ResitJointCourseSlot;
use App\Services\ResitTimetable\Support\ResitCandidates;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Finds the joint resits of a term (academic year and resit exam type): the
 * courses students of two or more specialties resit. Each gets one joint
 * resit (resit_jcs) linked to every resit exam that resits it.
 *
 * Who resits what changes as marks come in, so a term is synced again
 * whenever it is read; a course nobody else resits any more stops being
 * joint and its time is dropped.
 */
class ResitJointCourseSync
{
    public function __construct(private ResitCandidates $candidates) {}

    /** @return int how many joint resits the resit exam is linked to */
    public function forResitExam(string $schoolBranchId, string $resitExamId): int
    {
        $resitExam = ResitExam::where('school_branch_id', $schoolBranchId)->with('schoolYear')->find($resitExamId);
        $yearId = $resitExam?->schoolYear?->system_academic_year_id;

        if (!$resitExam || !$yearId || !$resitExam->exam_type_id) {
            return 0;
        }

        $this->forTerm($schoolBranchId, $yearId, $resitExam->exam_type_id);

        return ResitJointCourseRef::where('school_branch_id', $schoolBranchId)->where('resit_exam_id', $resitExam->id)->count();
    }

    /** Syncs every joint resit of a term. */
    public function forTerm(string $schoolBranchId, string $yearId, string $examTypeId): void
    {
        $resitExams = $this->resitExamsOfTerm($schoolBranchId, $yearId, $examTypeId);

        // course_id => resit exam ids whose candidates resit it
        $byCourse = [];
        foreach ($resitExams as $resitExam) {
            foreach (array_keys($this->candidates->byCourse($schoolBranchId, $resitExam)) as $courseId) {
                $byCourse[$courseId][] = $resitExam->id;
            }
        }
        $joint = array_filter($byCourse, fn(array $ids) => count(array_unique($ids)) > 1);

        $existing = ResitJointCourse::where('school_branch_id', $schoolBranchId)
            ->where('school_year_id', $yearId)
            ->where('exam_type_id', $examTypeId)
            ->get()
            ->keyBy('course_id');

        // No longer shared: not a joint resit any more.
        $stale = $existing->reject(fn(ResitJointCourse $course) => isset($joint[$course->course_id]));
        if ($stale->isNotEmpty()) {
            $ids = $stale->pluck('id');
            ResitJointCourseSlot::whereIn('resit_jc_id', $ids)->delete();
            ResitJointCourseRef::whereIn('resit_jc_id', $ids)->delete();
            ResitJointCourse::whereIn('id', $ids)->delete();
        }

        $now = now();
        foreach ($joint as $courseId => $resitExamIds) {
            $jointCourse = $existing[$courseId] ?? null;
            if (!$jointCourse) {
                $jointCourse = ResitJointCourse::create([
                    'school_branch_id' => $schoolBranchId,
                    'course_id' => $courseId,
                    'school_year_id' => $yearId,
                    'exam_type_id' => $examTypeId,
                ]);
            }

            $wanted = array_values(array_unique($resitExamIds));
            $linked = ResitJointCourseRef::where('resit_jc_id', $jointCourse->id)->pluck('resit_exam_id')->all();

            ResitJointCourseRef::where('resit_jc_id', $jointCourse->id)->whereNotIn('resit_exam_id', $wanted)->delete();

            $missing = array_diff($wanted, $linked);
            if ($missing) {
                ResitJointCourseRef::insert(array_map(fn(string $resitExamId) => [
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'resit_exam_id' => $resitExamId,
                    'resit_jc_id' => $jointCourse->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], array_values($missing)));
            }
        }
    }

    /** @return Collection<ResitExam> */
    public function resitExamsOfTerm(string $schoolBranchId, string $yearId, string $examTypeId): Collection
    {
        return ResitExam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $examTypeId)
            ->whereHas('schoolYear', fn($query) => $query->where('system_academic_year_id', $yearId))
            ->get();
    }
}
