<?php

namespace App\Services\JointCourse;

use App\Models\Course\SemesterJointCourse;

class SemesterJointCourseService
{
    public function getSemesterJointCourse(object $currentSchool)
    {
        return SemesterJointCourse::Where("school_branch_id", $currentSchool->id)
            ->with(['schoolYear', 'semester', 'course'])
            ->get()->map(fn($course) => [
                "id" => $course->id,
                "course_title" => $course->course->course_title,
                "course_code" => $course->course->course_code,
                "course_credit" => $course->course->credit,
                "course_decription" => $course->course->description ?? null,
                "semester" => $course->semester->name,
                "academic_year" => $course->schoolYear->name,
                "created_at" => $course->created_at,
                "updated_at" => $course->updated_at
            ]);
    }
}
