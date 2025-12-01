<?php

namespace App\Services\Course;

use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Exceptions\AppException;
use App\Models\Courses;
use Illuminate\Support\Facades\DB;
use App\Events\Actions\AdminActionEvent;

class TeacherCoursePreferenceService
{

    public function assignTeacherCoursePreference($currentSchool, $data, $authAdmin): array
    {
        $schoolBranchId = $currentSchool->id;
        $teacherId      = $data['teacher_id'];
        $requestedCourses = collect($data['coursesId']);

        if ($requestedCourses->isEmpty()) {
            throw new AppException(
                "No Courses Provided",
                400,
                "No courses selected",
                "Please select at least one course to assign."
            );
        }

        $requestedCourseIds = $requestedCourses->pluck('course_id')->unique()->values();


        $existing = TeacherCoursePreference::where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->whereIn('course_id', $requestedCourseIds)
            ->exists();

        if ($existing) {
            throw new AppException(
                "Duplicate Assignment",
                409,
                "Some courses already assigned",
                "One or more selected courses are already assigned to this teacher."
            );
        }

        $allowedSpecialties = TeacherSpecailtyPreference::where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->pluck('specialty_id')
            ->toArray();

        if (empty($allowedSpecialties)) {
            throw new AppException(
                "No Teaching Preference",
                403,
                "Teacher has no specialty preference",
                "This teacher has not set any preferred teaching specialties yet."
            );
        }

        $validCourseIds = Courses::where('school_branch_id', $schoolBranchId)
            ->whereIn('specialty_id', $allowedSpecialties)
            ->whereIn('id', $requestedCourseIds)
            ->pluck('id')
            ->toArray();

        $invalidCourses = $requestedCourseIds->diff($validCourseIds);

        if ($invalidCourses->isNotEmpty()) {
            throw new AppException(
                "Invalid Course Assignment",
                403,
                "Course-specialty mismatch",
                "Some courses do not belong to the teacher's preferred teaching specialties."
            );
        }


        $insertData = $requestedCourseIds->map(function ($courseId) use ($teacherId, $schoolBranchId) {
            return [
                'course_id'        => $courseId,
                'teacher_id'       => $teacherId,
                'school_branch_id' => $schoolBranchId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        })->toArray();

        TeacherCoursePreference::insert($insertData);

        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.teacherCoursePreference.assign"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "teacherCoursePreferenceManagement",
                "authAdmin" => $authAdmin,
                "data" => $insertData,
                "message" => "Course Assigned to teacher",
            ]
        );
        return [
            'teacher_id' => $teacherId,
            'course_count' => count($insertData),
            'school_branch_id' => $schoolBranchId,
        ];
    }
    public function getAssignableTeacherCourses($currentSchool, $teacherId)
    {
        $schoolBranchId = $currentSchool->id;

        $preferredSpecialtyIds = TeacherSpecailtyPreference::where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->pluck('specialty_id')
            ->toArray();

        if (empty($preferredSpecialtyIds)) {
            throw new AppException(
                "No Teaching Preference",
                403,
                "Teacher has no specialty preference",
                "This teacher has not set any preferred teaching specialties yet."
            );
        }

        $courses = Courses::where('school_branch_id', $schoolBranchId)
            ->whereIn('specialty_id', $preferredSpecialtyIds)
            ->with(['specialty', 'level'])
            ->select('id', 'course_code', 'course_title', 'credit', 'specialty_id', 'level_id');


        $courses->whereDoesntHave('teacherPreferences', function ($query) use ($schoolBranchId, $teacherId) {
            $query->where('teacher_id', $teacherId)
                ->where('school_branch_id', $schoolBranchId);
        });

        return $courses->get()->map(function ($course) {
            return [
                'course_id'       => $course->id,
                'course_code'     => $course->course_code,
                'course_title'    => $course->course_title,
                'credit'          => $course->credit ?? 0,
                'specialty_name'  => $course->specialty?->specialty_name ?? 'N/A',
                'level_name'      => $course->level?->name ?? 'N/A',
            ];
        })->values();
    }
    public function removeTeacherAssignedCourses($currentSchool, $data, $authAdmin)
    {
        $schoolBranchId = $currentSchool->id;
        $teacherId      = $data['teacher_id'] ?? null;
        $courseIds      = collect($data['coursesId'] ?? [])->pluck('course_id')->filter()->unique()->values();

        if (!$teacherId) {
            throw new AppException(
                "Invalid Request",
                400,
                "Teacher ID missing",
                "Teacher ID is required to remove course assignments."
            );
        }

        if ($courseIds->isEmpty()) {
            throw new AppException(
                "No Courses Selected",
                400,
                "No courses provided",
                "Please select at least one course to remove."
            );
        }

        $assigned = DB::table('teacher_course_preferences')
            ->where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->whereIn('course_id', $courseIds)
            ->pluck('course_id')
            ->toArray();

        if (empty($assigned)) {
            throw new AppException(
                "No Courses to Remove",
                409,
                "No matching assignments found",
                "None of the selected courses are currently assigned to this teacher."
            );
        }


        $deleted = DB::table('teacher_course_preferences')
            ->where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->whereIn('course_id', $assigned)
            ->delete();

        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.teacherCoursePreference.remove"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "teacherCoursePreferenceManagement",
                "authAdmin" => $authAdmin,
                "data" => $assigned,
                "message" => "Course Assigned to teacher",
            ]
        );
        return [
            'teacher_id' => $teacherId,
            'removed_courses_count' => $deleted,
            'removed_course_ids' => $assigned,
            'school_branch_id' => $schoolBranchId,
            'requested_count' => $courseIds->count(),
        ];
    }
    public function getAssignedTeacherCourses($currentSchool, $teacherId)
    {
        $schoolBranchId = $currentSchool->id;

        $assignedCourses = TeacherCoursePreference::where('school_branch_id', $schoolBranchId)
            ->where('teacher_id', $teacherId)
            ->with(['course.specialty', 'course.level'])
            ->get()
            ->pluck('course');

        $assignedCourses = $assignedCourses->filter();

        if ($assignedCourses->isEmpty()) {
            return [];
        }

        return $assignedCourses->map(function ($course) {
            return [
                'course_id'       => $course->id,
                'course_code'     => $course->course_code,
                'course_title'    => $course->course_title,
                'credit'          => $course->credit ?? 0,
                'specialty_name'  => $course->specialty?->specialty_name ?? 'N/A',
                'level_name'      => $course->level?->name ?? 'N/A',
            ];
        })->values();
    }
}
