<?php

namespace App\Services\JointCourse;

use App\Models\Courses;
use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Events\Analytics\OperationalAnalyticsEvent;
use App\Constant\Analytics\Operational\OperationalAnalyticsEvent as OperationalEvent;
use App\Models\Course\CourseSpecialty;
use Illuminate\Support\Facades\DB;

class JointCourseService
{
    public function createJointCourse(object $currentSchool, array $data,  $authAdmin)
    {
        if(count($data['specialtyIds']) < 2){
            throw new AppException(
                "A joint course must be associated with at least two specialties",
                400,
                "Invalid Joint Course",
                "Please select at least two specialties to associate with this joint course.",
                null
            );
        }

        $specialties = Specialty::where("school_branch_id", $currentSchool->id)
            ->whereIn("id", $data["specialtyIds"])
            ->get();

        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("course_code", $data['course_code'])
            ->where("course_title", $data['course_title'])
            ->first();
        if ($courses) {
            throw new AppException(
                "It looks like you already have a course with this title and code",
                400,
                "Duplicate Course",
                "Please choose a different title or code for this course.",
                '/courses'
            );
        }

        $course = new Courses();
        $course->course_code = $data['course_code'];
        $course->course_title = $data['course_title'];
        $course->credit = $data['credit'];
        $course->school_branch_id = $currentSchool->id;
        $course->semester_id = $data['semester_id'];
        $course->description = $data['description'] ?? null;
        $course->save();

        if (!empty($data['typeIds'])) {
            $syncData = collect($data['typeIds'])
                ->pluck('type_id')
                ->mapWithKeys(fn($typeId) => [
                    $typeId => ['school_branch_id' => $currentSchool->id],
                ])
                ->toArray();

            $course->types()->sync($syncData);
        }

        foreach ($specialties as $specialty) {
            CourseSpecialty::create([
                'school_branch_id' => $specialty->school_branch_id,
                'specialty_id' => $specialty->id,
                'course_id' => $course->id
            ]);
        }

        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.course.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "courseManagement",
                "action" => "course.created",
                "authAdmin" => $authAdmin,
                "data" => $course,
                "message" => "Course Created",
            ]
        );
        StudentActionEvent::dispatch([
            'schoolBranch'  => $currentSchool->id,
            'specialtyIds'  => [$specialty->id],
            'feature'       => 'courseCreate',
            'message'       => "New Course Created",
            'data'          => $course,
        ]);
        event(new OperationalAnalyticsEvent(
            eventType: OperationalEvent::COURSE_CREATED,
            version: 1,
            payload: [
                "school_branch_id" => $currentSchool,
                "specialty_id" => $specialty->id,
                "department_id" => $specialty->department_id,
                "level_id" => $specialty->level_id,
                "value" => 1
            ]
        ));
        return $course;
    }
    public function updateJointCourse(array $updateData, string $jointCourseId, $authAdmin, object $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)
            ->find($jointCourseId);

        if (!$course) {
            throw new AppException(
                "Course not found please try again",
                404,
                "Course Not Found",
                "The course you are trying to update does not exist or has already been deleted.",
                null
            );
        }

        $filteredData = array_filter($updateData, fn($v) => !is_null($v) && $v !== '');

        if (isset($filteredData['course_code']) || isset($filteredData['course_title'])) {
            $existingCourse = Courses::where("school_branch_id", $currentSchool->id)
                ->where(function ($query) use ($filteredData) {
                    if (isset($filteredData['course_code'])) {
                        $query->where('course_code', $filteredData['course_code']);
                    }
                    if (isset($filteredData['course_title'])) {
                        $query->where('course_title', $filteredData['course_title']);
                    }
                })
                ->where('id', '!=', $course->id)
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

        if (array_key_exists('typeIds', $updateData)) {
            $syncData = collect($updateData['typeIds'] ?? [])
                ->pluck('type_id')
                ->mapWithKeys(fn($typeId) => [
                    $typeId => ['school_branch_id' => $currentSchool->id],
                ])
                ->toArray();

            $course->types()->sync($syncData);
        }

        if (array_key_exists('specialtyIds', $updateData)) {
            $newSpecialtyIds = collect($updateData['specialtyIds'])
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $currentSpecialtyIds = DB::table('joint_courses')
                ->where('course_id', $course->id)
                ->where('school_branch_id', $currentSchool->id)
                ->pluck('specialty_id')
                ->toArray();

            $validNewSpecialtyIds = [];
            if (!empty($newSpecialtyIds)) {
                $validNewSpecialtyIds = Specialty::where('school_branch_id', $currentSchool->id)
                    ->whereIn('id', $newSpecialtyIds)
                    ->pluck('id')
                    ->toArray();
            }

            $toRemove = array_diff($currentSpecialtyIds, $validNewSpecialtyIds);
            $toAdd    = array_diff($validNewSpecialtyIds, $currentSpecialtyIds);

            if (!empty($toRemove)) {
                DB::table('joint_courses')
                    ->where('course_id', $course->id)
                    ->where('school_branch_id', $currentSchool->id)
                    ->whereIn('specialty_id', $toRemove)
                    ->delete();
            }

            if (!empty($toAdd)) {
                $insertData = collect($toAdd)->map(function ($specialtyId) use ($currentSchool, $course) {
                    return [
                        'school_branch_id' => $currentSchool->id,
                        'specialty_id'     => $specialtyId,
                        'course_id'        => $course->id,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                })->toArray();

                DB::table('joint_courses')->insert($insertData);
            }
        }

        $course->update($filteredData);

        AdminActionEvent::dispatch([
            "permissions"   => ["schoolAdmin.course.update"],
            "roles"         => ["schoolSuperAdmin", "schoolAdmin"],
            "schoolBranch"  => $currentSchool->id,
            "feature"       => "courseManagement",
            "action"        => "course.updated",
            "authAdmin"     => $authAdmin,
            "data"          => $course,
            "message"       => "Course Updated",
        ]);

        $studentSpecialtyIds = DB::table('joint_courses')
            ->where('course_id', $course->id)
            ->where('school_branch_id', $currentSchool->id)
            ->pluck('specialty_id')
            ->filter()
            ->values()
            ->toArray();

        if (empty($studentSpecialtyIds) && $course->specialty_id) {
            $studentSpecialtyIds = [$course->specialty_id];
        }

        StudentActionEvent::dispatch([
            'schoolBranch'  => $currentSchool->id,
            'specialtyIds'  => $studentSpecialtyIds,
            'feature'       => 'courseUpdate',
            'message'       => "Course Updated",
            'data'          => $course,
        ]);

        return $course;
    }
    public function deleteJointCourse(string $jointCourseId, object $currentSchool, $authAdmin)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)
            ->find($jointCourseId);

        if (!$course) {
            throw new AppException(
                "Course not found please try again",
                404,
                "Course Not Found",
                "The course you are trying to delete does not exist or has already been deleted.",
                null
            );
        }

        $course->delete();

        AdminActionEvent::dispatch([
            "permissions" => ["schoolAdmin.course.delete"],
            "roles" => ["schoolSuperAdmin", "schoolAdmin"],
            "schoolBranch" => $currentSchool->id,
            "feature" => "courseManagement",
            "action" => "course.deleted",
            "authAdmin" => $authAdmin,
            "data" => $course,
            "message" => "Course Deleted",
        ]);

        StudentActionEvent::dispatch([
            'schoolBranch'  => $currentSchool->id,
            'specialtyIds'  => [$course->specialty_id],
            'feature'       => 'courseDelete',
            'message'       => "Course Deleted",
            'data'          => $course,
        ]);

        return true;
    }
    public function getJointCourseDetails(string $jointCourseId, object $currentSchool)
    {
        $course = Courses::where("school_branch_id", $currentSchool->id)
            ->with(['types', 'courseSpecialty.specialty'])
            ->find($jointCourseId);

        if (!$course) {
            throw new AppException(
                "Course not found please try again",
                404,
                "Course Not Found",
                "The course you are trying to view does not exist or has already been deleted.",
                null
            );
        }

        return $course;
    }
    public function getJointCourses(object $currentSchool)
    {
        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->with(['courseSpecialty.specialty.level', 'courseSpecialty.specialty.department', 'semester', 'types'])
            ->withCount('courseSpecialty')
            ->having('course_specialty_count', '>', 1)
            ->get();

        if($courses->isEmpty()){
            throw new AppException(
                "No joint courses found for this school",
                404,
                "No Joint Courses",
                "There are currently no joint courses available for this school. Please check back later or contact support for more information.",
                null
            );
        }

        return $courses->map(fn($course) => [
            'id' => $course->id,
            'course_code' => $course->course_code,
            'course_title' => $course->course_title,
            'credit' => $course->credit,
            'semester_title' => $course->semester->name ?? null,
            'specialty_count' => $course->courseSpecialty->count(),
        ])->toArray();
    }
}
