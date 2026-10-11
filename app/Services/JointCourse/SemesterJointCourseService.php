<?php

namespace App\Services\JointCourse;

use App\Models\Course\SemesterJointCourse;

class SemesterJointCourseService
{

    public function getSemesterJointCourse(object $currentSchool)
    {
        return SemesterJointCourse::where('school_branch_id', $currentSchool->id)
            ->with([
                'schoolYear',
                'semester',
                'course',
                'jointCourseSlots',
                'semesterJointCourseRef.schoolSemester',
            ])
            ->get()
            ->map(function ($course) {
                $refs = $course->semesterJointCourseRef;
                $slots = $course->jointCourseSlots;

                $now = now();

                $hasActiveSemester = $refs->contains(function ($ref) use ($now) {
                    $semester = $ref->schoolSemester;

                    return $semester
                        && $semester->start_date <= $now
                        && $semester->end_date >= $now;
                });

                $hasUpcomingSemester = $refs->contains(function ($ref) use ($now) {
                    $semester = $ref->schoolSemester;

                    return $semester
                        && $semester->start_date > $now;
                });

                $semesterStatus = match (true) {
                    $hasActiveSemester => 'active',
                    $hasUpcomingSemester => 'upcoming',
                    $refs->isEmpty() => 'no_schedule',
                    default => 'expired',
                };

                $slotCount = $slots->count();
                $slotConfigStatus = $slotCount > 0 ? 'configured' : 'not_configured';

                return [
                    'id' => $course->id,
                    'course_title' => $course->course->course_title,
                    'course_code' => $course->course->course_code,
                    'course_credit' => $course->course->credit,
                    'course_description' => $course->course->description ?? null,

                    'semester' => $course->semester->name,
                    'academic_year' => $course->schoolYear->name,
                    'academic_year_start' => $course->schoolYear->year_start,
                    'academic_year_end' => $course->schoolYear->year_end,

                    'affected_specialties' => $refs->count(),
                    'semester_status' => $semesterStatus,

                    'slot_count' => $slotCount,
                    'slot_config_status' => $slotConfigStatus,

                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ];
            });
    }

    public function getAffectedSpecialtiesSemesterJointCourseId(object $currentSchool, string $semesterJointCourseId)
    {
        $semesterJointCourse = SemesterJointCourse::where('school_branch_id', $currentSchool->id)
            ->where('id', $semesterJointCourseId)
            ->with([
                'semesterJointCourseRef.schoolSemester.specialty.level',
            ])
            ->first();

        if (!$semesterJointCourse) {
            return collect();
        }

        return $semesterJointCourse->semesterJointCourseRef
            ->pluck('schoolSemester.specialty')
            ->filter()
            ->unique('id')
            ->values();
    }
}
