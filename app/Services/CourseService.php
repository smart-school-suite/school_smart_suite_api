<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Jobs\StatisticalJobs\OperationalJobs\CourseStatJob;
use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Student;
use App\Models\Specialty;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class CourseService
{
    // Implement your logic here
    public function createCourse(array $data, $currentSchool): Courses
    {
        $specialty = Specialty::findOrFail($data['specialty_id']);
        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("course_code", $data['course_code'])
            ->where("course_title", $data['course_title'])
            ->first();
        if (!$courses) {
            throw new AppException(
                "It looks like you already have a course with this title and code",
                400,
                "Duplicate Course",
                "Please choose a different title or code for this course.",
                '/courses'
            );
        }
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
            throw new AppException(
                "Course not found",
                404,
                "Course Not Found",
                "The course you are trying to delete does not exist or has already been deleted.",
                null
            );
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
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more courses not found",
                404,
                "One or more courses not found",
                "One or more courses you are trying to delete do not exist or have already been deleted.",
                null
            );
        }
    }
    public function updateCourse(string $courseId, array $data, $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if (!$course) {
            throw new AppException(
                "Course not found please try again",
                404,
                "Course Not Found",
                "The course you are trying to update does not exist or has already been deleted.",
                null
            );
        }

        $filteredData = array_filter($data);
        if ($filteredData['course_code'] || $filteredData['course_title']) {
            $existingCourse = Courses::where("school_branch_id", $currentSchool->id)
                ->where(function ($query) use ($filteredData) {
                    if (isset($filteredData['course_code'])) {
                        $query->where('course_code', $filteredData['course_code']);
                    }
                    if (isset($filteredData['course_title'])) {
                        $query->where('course_title', $filteredData['course_title']);
                    }
                })
                ->where('id', '!=', $courseId)
                ->first();
            if ($existingCourse) {
                throw new AppException(
                    "It looks like you already have a course with this title and code",
                    400,
                    "Duplicate Course Details",
                    "Please choose a different title or code for this course.",
                    '/courses'
                );
            }
        }

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
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We were unable to complete the Update due to a server error.",
                "/courses"
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more courses not found",
                404,
                "One or more courses not found",
                "One or more courses you are trying to update do not exist or have already been deleted.",
                "/courses"
            );
        }
    }
    public function getCourses($currentSchool)
    {
        $courses  = Courses::where("school_branch_id", $currentSchool->id)
            ->with(['department', 'specialty', 'semester', 'level'])
            ->get();

        if ($courses->isEmpty()) {
            throw new AppException(
                "No courses found",
                404,
                "No Courses Found",
                "There are no courses available for this school branch.",
                "/courses"
            );
        }

        return $courses;
    }
    public function courseDetails(string $courseId, $currentSchool)
    {
        try {
            $course = Courses::where('school_branch_id', $currentSchool->id)
                ->with(['department', 'specialty', 'semester', 'level'])
                ->findorFail($courseId);
            return $course;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Course not found",
                404,
                "Course Not Found",
                "The course you are trying to view does not exist or has already been deleted.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the course details.",
                null
            );
        }
    }
    public function getCoursesBySpecialtySemesterAndLevel($currentSchool, string $specialtyId,  string $semesterId)
    {
        try {
            $specialty = Specialty::findorFail($specialtyId);

            $levelId = $specialty->level->id;

            $coursesData = Courses::where("school_branch_id", $currentSchool->id)
                ->where("semester_id", $semesterId)
                ->where("specialty_id", $specialtyId)
                ->where("level_id", $levelId)
                ->get();

            if ($coursesData->isEmpty()) {
                throw new AppException(
                    "No courses found for the specified specialty, semester, and level.",
                    404,
                    "No Courses Found",
                    "There are no courses available for the selected specialty, semester, and level combination.",
                    "/courses"
                );
            }
            return $coursesData;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the course details.",
                "/details"
            );
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Specialty Not Found",
                404,
                "Specialty Not Found",
                "The Selected Specialty Was Not Found, It must have been deleted or removed",
                "/specialty"
            );
        }
    }
    public function deactivateCourse($currentSchool, string $courseId)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if ($course->status === "inactive") {
            throw new AppException("Course is already inactive", 400, "Course Already Inactive", "The course you are trying to deactivate is already inactive.", null);
        }
        if (!$course) {
            throw new AppException("Course not found", 404, "Course Not Found", "The course you are trying to deactivate does not exist or has already been deleted.", null);
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
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while trying to deactivate courses.",
                "/courses"
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more courses not found",
                404,
                "One or more courses not found",
                "One or more courses you are trying to update do not exist or have already been deleted.",
                "/courses"
            );
        }
    }
    public function activateCourse($currentSchool, string $courseId)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)->find($courseId);
        if ($course->status === "active") {
            throw new AppException("Course is already active", 400, "Course Already Active", "The course you are trying to activate is already active.", null);
        }
        if (!$course) {
            throw new AppException("Course not found", 404, "Course Not Found", "The course you are trying to activate does not exist or has already been deleted.", null);
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
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while trying to activate courses.",
                "/courses"
            );
        }
    }
    public function getActiveCourses($currentSchool)
    {
        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("status", "active")
            ->with(['department', 'specialty', 'semester', 'level'])
            ->get();
        if ($courses->isEmpty()) {
            throw new AppException(
                "No Active Courses Found for this school Branch",
                404,
                "Courses Found",
                "No Courses Found it looks like some courses have been deleted or deactivated",
                "/courses"
            );
        }
        return $courses;
    }
    public function getCoursesBySchoolSemester($currentSchool, string $semesterId, string $specialtyId)
    {
        try {
            $schoolSemester = SchoolSemester::findOrFail($semesterId);
            $specialty = Specialty::findOrFail($specialtyId);
            $courses = Courses::where("school_branch_id", $currentSchool->id)->where("semester_id", $schoolSemester->semester_id)
                ->where("specialty_id", $specialty->id)
                ->where("status", "active")
                ->get();
            if ($courses->isEmpty()) {
                throw new AppException(
                    "No Active Courses Found for this school Branch",
                    404,
                    "Courses Found",
                    "No Courses Found it looks like some courses have been deleted or deactivated",
                    "/courses"
                );
            }
            return $courses;
        } catch (AppException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Semester or Specialty Not Found",
                404,
                "Semester or Specialty Not Found",
                "The Selected Semester or Specialty Was Not Found, It must have been deleted or removed",
                "/details"
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We encountered an unexpected issue while retrieving the course details.",
                "/details"
            );
        }
    }
    public function getAllCoursesByStudentId(object $currentSchool, string $studentId): array
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($studentId);

        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Retrieval Error",
                "The student ID {$studentId} could not be found in this school branch. Please verify the ID.",
                "/students"
            );
        }

        if (empty($student->specialty_id) || empty($student->level_id)) {
            throw new AppException(
                "Student Profile Incomplete",
                400,
                "Course Matching Failed",
                "The student's specialty or level information is missing, preventing course assignment lookup.",
                "/students/profile"
            );
        }

        $courses = Courses::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where("status", "active")
            ->with('semester')
            ->get();

        if ($courses->isEmpty()) {
            throw new AppException(
                "No Active Courses Found",
                404,
                "Courses Not Configured",
                "No courses are currently configured for the student's specialty and level in this school branch.",
                "/courses/configuration"
            );
        }

        $groupedCourses = $this->groupAndFormatCourses($courses);

        if (empty($groupedCourses)) {
            throw new AppException(
                "Course Grouping Failed",
                500,
                "Internal Processing Error",
                "Courses were found, but the system failed to organize them by semester. Check the 'semester' relationship.",
                "/system/logs"
            );
        }

        return $groupedCourses;
    }
    public function getCoursesByStudentIdSemesterId($currentSchool, string $studentId, string $semesterId){
          $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($studentId);

        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Retrieval Error",
                "The student ID {$studentId} could not be found in this school branch. Please verify the ID.",
                "/students"
            );
        }

        if (empty($student->specialty_id) || empty($student->level_id)) {
            throw new AppException(
                "Student Profile Incomplete",
                400,
                "Course Matching Failed",
                "The student's specialty or level information is missing, preventing course assignment lookup.",
                "/students/profile"
            );
        }

        $courses = Courses::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where("semester_id", $semesterId)
            ->where("status", "active")
            ->with('semester')
            ->get();

        if ($courses->isEmpty()) {
            throw new AppException(
                "No Active Courses Found",
                404,
                "Courses Not Configured",
                "No courses are currently configured for the student's specialty and level in this school branch.",
                "/courses/configuration"
            );
        }

        return $courses;
    }
       protected function groupAndFormatCourses(Collection $courses): array
    {
        $grouped = $courses->groupBy(function ($course) {
            return optional($course->semester)->name ?? 'Unassigned Semester';
        });

        $formatted = $grouped->map(function (Collection $coursesInSemester, string $semesterName) {
            $firstCourse = $coursesInSemester->first();
            $semesterId = optional($firstCourse)->semester_id;

            return [
                'semesterId' => $semesterId,
                'semester' => $semesterName,
                'courses' => $coursesInSemester->map(function ($course) {
                    return $course->only([
                        'id',
                        'course_code',
                        'course_title',
                        'credit',
                        'description',
                        'status'
                    ]);
                })->toArray(),
            ];
        })->values();

        return $formatted->toArray();
    }
}
