<?php

namespace App\Services;

use App\Models\Courses;
use App\Models\Specialty;

class courseService
{
    // Implement your logic here

    public function createCourse(array $data, $currentSchool): Courses
    {
        $course = new Courses();
        $course->course_code = $data['course_code'];
        $course->course_title = $data['course_title'];
        $course->specialty_id = $data['specialty_id'];
        $course->department_id = $data['department_id'];
        $course->credit = $data['credit'];
        $course->school_branch_id = $currentSchool->id;
        $course->semester_id = $data['semester_id'];
        $course->level_id = $data['level_id'];
        $course->save();

        return $course;
    }

    public function deleteCourse(string $course_id, $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool)->find($course_id);
        if (!$course) {
            return ApiResponseService::error('Course Not found', null, 404);
        }
        $course->delete();
        return $course;
    }

    public function updateCourse(string $course_id, array $data, $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool)->find($course_id);
        if (!$course) {
            return ApiResponseService::error('Course Not found', null, 404);
        }

        $filteredData = array_filter($data);

        $course->update($filteredData);

        return $course;
    }

    public function getCourses($currentSchool)
    {
        return Courses::where("school_branch_id", $currentSchool->id)
            ->with(['department', 'specialty'])
            ->get();
    }

    public function courseDetails(string $course_id, $currentSchool)
    {
        $course = Courses::where('school_branch_id', $currentSchool)->find($course_id);

        if (!$course) {
            return ApiResponseService::error("Course Not found", null, 404);
        }

        return $course;
    }

    public function getCoursesBySpecialtySemesterAndLevel($currentSchool, string $specialtyId,  string $semesterId)
    {
        $specialty = Specialty::find($specialtyId);
        if (!$specialty) {
            return ApiResponseService::error("Specailty not found", null, 400);
        }

        $levelId = $specialty->level->id;

        $coursesData = Courses::where("school_branch_id", $currentSchool->id)
            ->where("semester_id", $semesterId)
            ->where("specialty_id", $specialtyId)
            ->where("level_id", $levelId)
            ->get();
        return $coursesData;
    }
}
