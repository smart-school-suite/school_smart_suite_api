<?php

namespace App\Services\Course;

use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Exceptions\AppException;
use App\Models\Courses;
use Illuminate\Support\Facades\DB;
use App\Events\Actions\AdminActionEvent;
use Illuminate\Support\Str;
use App\Models\Teacher;

class TeacherCoursePreferenceService
{
    public function assignTeacherCoursePreference($currentSchool, $data, $authAdmin): array
    {
        return DB::transaction(function () use ($currentSchool, $data, $authAdmin) {
            $schoolBranchId = $currentSchool->id;
            $teacherId      = $data['teacher_id'];
            $requestedCourses = collect($data['courseIds'] ?? []);

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

            $teacher = Teacher::where('id', $teacherId)
                ->where('school_branch_id', $schoolBranchId)
                ->lockForUpdate()
                ->firstOrFail();

            $wasUnassigned = $teacher->course_assignment_status === 'unassigned';

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
                    "Some selected courses do not belong to the teacher's preferred teaching specialties."
                );
            }

            $insertData = $requestedCourseIds->map(function ($courseId) use ($teacherId, $schoolBranchId) {
                return [
                    'id'                => Str::uuid()->toString(),
                    'course_id'         => $courseId,
                    'teacher_id'        => $teacherId,
                    'school_branch_id'  => $schoolBranchId,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            })->toArray();

            TeacherCoursePreference::insert($insertData);

            $assignedCount = count($insertData);

            $teacher->increment('num_assigned_courses', $assignedCount);

            if ($wasUnassigned && $assignedCount > 0) {
                $teacher->course_assignment_status = 'assigned';
                $teacher->save();
            }

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.teacherCoursePreference.assign"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "teacherCoursePreferenceManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'teacher_id'   => $teacher->id,
                    'teacher_name' => $teacher->name ?? trim("{$teacher->first_name} {$teacher->last_name}"),
                    'courses_assigned' => $assignedCount,
                    'course_ids'   => $requestedCourseIds->toArray(),
                ],
                "message" => "Courses assigned to teacher successfully",
            ]);

            return [
                'teacher_id'       => $teacherId,
                'course_count'     => $assignedCount,
                'school_branch_id' => $schoolBranchId,
                'status_updated'   => $wasUnassigned,
            ];
        });
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


        $courses->whereDoesntHave('teacherCoursePreference', function ($query) use ($schoolBranchId, $teacherId) {
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
    public function removeTeacherAssignedCourses($currentSchool, $data, $authAdmin): array
    {
        return DB::transaction(function () use ($currentSchool, $data, $authAdmin) {
            $schoolBranchId = $currentSchool->id;
            $teacherId      = $data['teacher_id'] ?? null;
            $requestedCourseIds = collect($data['courseIds'] ?? [])
                ->pluck('course_id')
                ->filter()
                ->unique()
                ->values();

            if (!$teacherId) {
                throw new AppException(
                    "Teacher ID is required",
                    400,
                    "Invalid Request",
                    "Teacher ID must be provided to remove course assignments."
                );
            }

            if ($requestedCourseIds->isEmpty()) {
                throw new AppException(
                    "No courses selected",
                    400,
                    "No Courses Selected",
                    "Please select at least one course to remove from the teacher."
                );
            }

            $teacher = Teacher::where('id', $teacherId)
                ->where('school_branch_id', $schoolBranchId)
                ->lockForUpdate()
                ->firstOrFail();

            $assignedCourseIds = DB::table('teacher_course_preferences')
                ->where('school_branch_id', $schoolBranchId)
                ->where('teacher_id', $teacherId)
                ->whereIn('course_id', $requestedCourseIds)
                ->pluck('course_id')
                ->toArray();

            if (empty($assignedCourseIds)) {
                throw new AppException(
                    "No assigned courses found",
                    404,
                    "Nothing to Remove",
                    "None of the selected courses are currently assigned to this teacher."
                );
            }

            $deletedCount = DB::table('teacher_course_preferences')
                ->where('school_branch_id', $schoolBranchId)
                ->where('teacher_id', $teacherId)
                ->whereIn('course_id', $assignedCourseIds)
                ->delete();

            $teacher->decrement('num_assigned_courses', $deletedCount);

            if ($teacher->num_assigned_courses <= 0) {
                $teacher->num_assigned_courses = 0;
                $teacher->course_assignment_status = 'unassigned';
                $teacher->save();
            }

            $courseNames = Courses::whereIn('id', $assignedCourseIds)
                ->pluck('course_name', 'id')
                ->map(fn($name, $id) => "$name (ID: $id)")
                ->values()
                ->toArray();

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.teacherCoursePreference.remove"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "teacherCoursePreferenceManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'teacher_id'     => $teacher->id,
                    'teacher_name'   => $teacher->name ?? trim("{$teacher->first_name} {$teacher->last_name}"),
                    'removed_courses_count' => $deletedCount,
                    'removed_course_ids'    => $assignedCourseIds,
                    'removed_course_names'  => $courseNames,
                ],
                "message" => "Courses removed from teacher successfully",
            ]);

            return [
                'teacher_id'              => $teacherId,
                'removed_courses_count'   => $deletedCount,
                'removed_course_ids'      => $assignedCourseIds,
                'school_branch_id'        => $schoolBranchId,
                'requested_count'         => $requestedCourseIds->count(),
                'status_updated_to_unassigned' => $teacher->wasChanged('course_assignment_status'),
            ];
        });
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
