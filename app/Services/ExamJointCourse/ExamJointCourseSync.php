<?php

namespace App\Services\ExamJointCourse;

use App\Models\Course\CourseSpecialty;
use App\Models\ExamJointCourse\ExamJointCourse;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\Exams;
use Illuminate\Support\Str;

/**
 * Finds the courses of an exam that other specialties take too, and links
 * the exam to one joint exam per course, as CreateJointCourseSemesterJob does
 * for semesters. A joint exam is shared by every exam of the same exam type
 * and academic year whose specialty takes the course, so it gets one time
 * for all of them.
 */
class ExamJointCourseSync
{
    /** @return int how many joint courses the exam is now linked to */
    public function forExam(string $schoolBranchId, string $examId): int
    {
        $exam = Exams::where('school_branch_id', $schoolBranchId)
            ->with(['examtype', 'schoolYear'])
            ->find($examId);

        $specialtyId = $exam?->schoolYear?->specialty_id;
        $semesterId = $exam?->examtype?->semester_id;
        $yearId = $exam?->schoolYear?->system_academic_year_id;

        if (!$exam || !$specialtyId || !$semesterId || !$yearId) {
            return 0;
        }

        $sharedCourseIds = CourseSpecialty::query()
            ->where('school_branch_id', $schoolBranchId)
            ->whereHas('course', fn($query) => $query->where('semester_id', $semesterId))
            ->whereIn('course_id', CourseSpecialty::query()
                ->where('school_branch_id', $schoolBranchId)
                ->where('specialty_id', $specialtyId)
                ->select('course_id'))
            ->groupBy('course_id')
            ->havingRaw('COUNT(DISTINCT specialty_id) > 1')
            ->pluck('course_id')
            ->all();

        if (empty($sharedCourseIds)) {
            return 0;
        }

        $existing = ExamJointCourse::where('school_branch_id', $schoolBranchId)
            ->where('school_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('exam_type_id', $exam->exam_type_id)
            ->whereIn('course_id', $sharedCourseIds)
            ->pluck('id', 'course_id')
            ->all();

        $now = now();
        $new = [];
        foreach (array_diff($sharedCourseIds, array_keys($existing)) as $courseId) {
            $id = (string) Str::uuid();
            $existing[$courseId] = $id;
            $new[] = [
                'id' => $id,
                'school_branch_id' => $schoolBranchId,
                'course_id' => $courseId,
                'school_year_id' => $yearId,
                'semester_id' => $semesterId,
                'exam_type_id' => $exam->exam_type_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($new) {
            ExamJointCourse::insert($new);
        }

        $linked = ExamJointCourseRef::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', $exam->id)
            ->pluck('exam_js_id')
            ->flip()
            ->all();

        $refs = [];
        foreach ($existing as $jointCourseId) {
            if (!isset($linked[$jointCourseId])) {
                $refs[] = [
                    'id' => (string) Str::uuid(),
                    'school_branch_id' => $schoolBranchId,
                    'exam_id' => $exam->id,
                    'exam_js_id' => $jointCourseId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        if ($refs) {
            ExamJointCourseRef::insert($refs);
        }

        return count($existing);
    }
}
