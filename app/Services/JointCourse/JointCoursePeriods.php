<?php

namespace App\Services\JointCourse;

use App\Models\Course\JointCourseSlot;
use App\Models\Course\SemesterJointCourse;
use App\Schedular\SemesterTimetable\Solution\Support\Time;

class JointCoursePeriods
{
    public function forSchoolSemester(string $schoolBranchId, string $schoolSemesterId): array
    {
        $courses = SemesterJointCourse::where('school_branch_id', $schoolBranchId)
            ->whereHas('semesterJointCourseRef', fn($query) => $query->where('school_semester_id', $schoolSemesterId))
            ->with(['course', 'jointCourseSlots'])
            ->get();

        return [
            'periods' => $courses
                ->flatMap(fn(SemesterJointCourse $course) => $course->jointCourseSlots->map(fn(JointCourseSlot $slot) => [
                    'day' => strtolower($slot->day),
                    'start_time' => Time::toTime(Time::toMinutes($slot->start_time)),
                    'end_time' => Time::toTime(Time::toMinutes($slot->end_time)),
                    'course_id' => $course->course_id,
                    'teacher_id' => $slot->teacher_id,
                    'hall_id' => $slot->hall_id,
                ]))
                ->values()
                ->all(),
            'missing' => $courses
                ->filter(fn(SemesterJointCourse $course) => $course->jointCourseSlots->isEmpty())
                ->map(fn(SemesterJointCourse $course) => [
                    'semester_joint_course_id' => $course->id,
                    'course_id' => $course->course_id,
                    'course_title' => $course->course?->course_title ?? 'A joint course',
                ])
                ->values()
                ->all(),
        ];
    }
}
