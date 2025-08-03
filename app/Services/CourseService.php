<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\OperationalJobs\CourseStatJob;
use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseService
{
    // Implement your logic here
    public function createCourse(array $data, $currentSchool): Courses
    {
        $specialty = Specialty::findOrFail($data['specialty_id']);
        $courseId = Str::uuid();
        $course = new Courses();
        $course->id = $courseId;
        $course->course_code = $data['course_code'];
        $course->course_title = $data['course_title'];
        $course->specialty_id = $specialty->id;
        $course->department_id = $specialty->department_id;
        $course->credit = $data['credit'];
        $course->school_branch_id = $currentSchool->id;
        $course->semester_id = $data['semester_id'];
        $course->level_id = $specialty->level_id;
        $course->description = $data['description'] ?? null;
        $course->save();
        CourseStatJob::dispatch($currentSchool->id, $courseId);
        return $course;

    }
    public function deleteCourse(string $courseId, $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if (!$course) {
            return ApiResponseService::error('Course Not found', null, 404);
        }
        $course->delete();
        return $course;
    }
    public function bulkDeleteCourse($coursesIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($coursesIds as $courseId) {
                $course = Courses::findOrFail($courseId['course_id']);
                $course->delete();
                $result[] = $course;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function updateCourse(string $courseId, array $data, $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if (!$course) {
            return ApiResponseService::error('Course Not found', null, 404);
        }

        $filteredData = array_filter($data);

        $course->update($filteredData);

        return $course;
    }
    public function bulkUpdateCourse($updateCourseList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateCourseList as $updateCourse) {
                $course = Courses::findOrFail($updateCourse['course_id']);
                $filteredData = array_filter($updateCourse);
                $course->update($filteredData);
                $result[] = $course;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getCourses($currentSchool)
    {
        return Courses::where("school_branch_id", $currentSchool->id)
            ->with(['department', 'specialty', 'semester', 'level'])
            ->get();
    }
    public function courseDetails(string $courseId, $currentSchool)
    {
        $course = Courses::where('school_branch_id', $currentSchool->id)
            ->with(['department', 'specialty', 'semester', 'level'])
            ->find($courseId);
        if (!$course) {
            return ApiResponseService::error("Course not found please try again", null, 404);
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
    public function deactivateCourse($currentSchool, string $courseId)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if (!$course) {
            return ApiResponseService::success("Course not found", null, null, 400);
        }
        $course->status = "inactive";
        $course->save();
        return $course;
    }
    public function bulkDeactivateCourse($coursesIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($coursesIds as $courseId) {
                $course = Courses::findOrFail($courseId['course_id']);
                $course->status = 'inactive';
                $course->save();
                $result[] = [
                    $course
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function activateCourse($currentSchool, string $courseId)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if (!$course) {
            return ApiResponseService::success("Course not found", null, null, 400);
        }
        $course->status = "active";
        $course->save();
        return $course;
    }
    public function bulkActivateCourse($courseIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($courseIds as $courseId) {
                $course = Courses::findOrFail($courseId['course_id']);
                $course->status = 'active';
                $course->save();
                $result[] = [
                    $course
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getActiveCourses($currentSchool)
    {
        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("status", "active")
            ->with(['department', 'specialty', 'semester', 'level'])
            ->get();
        return $courses;
    }
    public function getCoursesBySchoolSemester($currentSchool, string $semesterId, string $specialtyId)
    {
        $schoolSemester = SchoolSemester::findOrFail($semesterId);
        $specialty = Specialty::findOrFail($specialtyId);
        $courses = Courses::where("school_branch_id", $currentSchool->id)->where("semester_id", $schoolSemester->semester_id)
            ->where("specialty_id", $specialty->id)
            ->where("status", "active")
            ->get();
        return $courses;
    }
}
