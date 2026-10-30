<?php

namespace App\Services\Teacher;

use App\Models\Specialty;
use App\Models\Teacher;
use App\Notifications\SpecialtyAssignedToTeacher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Exceptions\AppException;
use Carbon\Carbon;
use App\Services\ApiResponseService;
use App\Models\Teacher\TeacherSpecialty;

class TeacherService
{
    public function getTeacherDetails(string $teacherId)
    {
        $teacher = Teacher::with(['qualifications', 'levels'])->findOrFail($teacherId);
        return $teacher;
    }
    public function deletetTeacher(string $teacherId, object $currentSchool, object $authAdmin)
    {
        $teacher = Teacher::where("school_branch_id", $currentSchool->id)
            ->findOrFail($teacherId);
        $teacher->delete();
        return $teacher;
    }
    public function updateTeacher(array $data, string $teacherId, object $currentSchool, object $authAdmin)
    {
        $teacher = Teacher::where("school_branch_id", $currentSchool->id)
            ->findOrFail($teacherId);

        if (
            isset($data['email']) &&
            Teacher::where('school_branch_id', $currentSchool->id)
                ->where('email', $data['email'])
                ->where('id', '!=', $teacher->id)
                ->exists()
        ) {
            throw new AppException(
                "This email address is already in use at this school branch.",
                409,
                "Email Already Exists",
                "The email '{$data['email']}' is already associated with another account in your school branch. Please use a different email.",
                null
            );
        }

        if (
            isset($data['name']) &&
            Teacher::where('school_branch_id', $currentSchool->id)
                ->where('name', $data['name'])
                ->where('id', '!=', $teacher->id)
                ->exists()
        ) {
            throw new AppException(
                "A teacher with the name '{$data['name']}' already exists at this school branch.",
                409,
                "Duplicate Teacher Name 📛",
                "Another teacher with the exact name '{$data['name']}' is already registered at this school. Consider adding a middle initial or suffix to differentiate the record.",
                null
            );
        }

        DB::transaction(function () use ($teacher, $data, $currentSchool) {
            $teacher->update(Arr::only($data, [
                'first_name',
                'last_name',
                'name',
                'email',
                'address',
                'gender_id',
                'phone',
            ]));

            if (array_key_exists('allowed_level_ids', $data)) {
                $levelsSyncData = [];
                foreach ($data['allowed_level_ids'] as $levelId) {
                    $levelsSyncData[$levelId] = [
                        'school_branch_id' => $currentSchool->id
                    ];
                }
                $teacher->levels()->sync($levelsSyncData);
            }

            if (array_key_exists('qualifications', $data)) {
                $qualificationsSyncData = [];
                foreach ($data['qualifications'] as $item) {
                    $qualificationsSyncData[$item['qualification_id']] = [
                        'school_branch_id' => $currentSchool->id,
                        'field_of_study'   => $item['field_of_study'],
                        'institution'      => $item['institution'],
                        'year'             => $item['year'],
                    ];
                }
                $teacher->qualifications()->sync($qualificationsSyncData);
            }
        });

        return $teacher->fresh(['qualifications', 'levels']);
    }
    public function getTeacherSchedule(string $teacherId, $currentSchool)
    {
        // $teacher_timetable_data = Timetable::where('school_branch_id', $currentSchool->id)
        //     ->where('teacher_id', $teacherId)
        //     ->with(['specialty', 'course', 'level'])
        //     ->get();

        // if ($teacher_timetable_data->isEmpty()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'No records found'
        //     ], 409);
        // }

        $time_table = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];

        // foreach ($teacher_timetable_data as $entry) {
        //     $day = strtolower($entry->day_of_week);

        //     if (array_key_exists($day, $time_table)) {
        //         $time_table[$day][] = [
        //             'level_name' => $entry->level->name,
        //             'level' => $entry->level->level,
        //             "specialty" => $entry->specialty->specialty_name,
        //             "course" => $entry->course->course_title,
        //             "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
        //             "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
        //             "teacher" => $entry->teacher->name
        //         ];
        //     }
        // }

        return $time_table;
    }
    public function getAllTeachers(object $currentSchool)
    {
        $getInstructors = Teacher::where("school_branch_id", $currentSchool->id)
            ->with(['gender', 'specialties', 'currentAvatar']) // Fixed typo: 'specailty' to 'specialty'
            ->withCount(['specialties as num_assigned_specialties']) // Count specialty preferences
            //  ->withCount(['teacherCoursePreferences as num_assigned_courses']) // Count assigned courses if needed
            ->get();

        return $getInstructors->map(fn($teacher) => [
            "id" => $teacher->id,
            "first_name" => $teacher->first_name ?? null,
            "last_name" => $teacher->last_name ?? null,
            'username' => $teacher->username ?? null,
            "name" => $teacher->name ?? null,
            "profile_picture" => $teacher->currentAvatar?->object_key ?? null,
            "gender" => $teacher->gender->name ?? null,
            "email" => $teacher->email ?? null,
            "status" => $teacher->status ?? null,
            "phone" => $teacher->phone ?? null,
            "created_at" => $teacher->created_at ?? null,
            "updated_at" => $teacher->updated_at ?? null,
            'num_assigned_specialties' => $teacher->num_assigned_specialties ?? 0,
            'specialty_assignment_status' => $teacher->specialty_assignment_status ?? $this->getSpecialtyAssignmentStatus($teacher->num_assigned_specialties ?? 0)
        ]);
    }
    private function getSpecialtyAssignmentStatus(int $count): string
    {
        if ($count >= 1) {
            return "assigned";
        }
        return "unassigned";
    }
    public function addSpecailtyPreference(array $preferenceData, object $currentSchool)
    {
        $result = [];
        $teacher = null;
        $specialties = [];
        $specialty = null;
        foreach ($preferenceData as $preference) {
            if ($specialty == null) {
                $specialtyDetails = Specialty::where("school_branch_id", $currentSchool->id)
                    ->with('level')
                    ->find($preference['specialty_id']);
                $specialty = $specialtyDetails;
            }
            if ($teacher == null) {
                $teacherDetails = Teacher::where("school_branch_id", $currentSchool->id)
                    ->find($preference['teacher_id']);
                $teacher = $teacherDetails;
            }
            $createdEntry = TeacherSpecialty::create([
                'specialty_id' => $preference["specialty_id"],
                'teacher_id' =>  $preference['teacher_id'],
                "school_branch_id" => $currentSchool->id
            ]);
            $result[] = $createdEntry;
            $specialties[] = "{$specialty->specialty_name}, {$specialty->level->name}";
        }
        $teacher->notify(new SpecialtyAssignedToTeacher($specialties));
        return $result;
    }
    public function deactivateTeacher(string $teacherId, object $currentSchool, object $authAdmin)
    {
        $teacher = Teacher::where("school_branch_id", $currentSchool->id)
            ->findOrFail($teacherId);
        $teacher->status = "inactive";
        $teacher->save();
        return $teacher;
    }
    public function activateTeacher(string $teacherId, object $currentSchool, object $authAdmin)
    {
        $teacher = Teacher::where("school_branch_id", $currentSchool->id)
            ->findOrFail($teacherId);
        $teacher->status = "active";
        $teacher->save();
        return $teacher;
    }
    public function bulkDeactivateTeacher(array $teacherIds, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($teacherId['teacher_id']);
                $teacher->status = "inactive";
                $teacher->save();
                $result[] = [
                    $teacher
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkActivateTeacher(array $teacherIds, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($teacherId['teacher_id']);
                $teacher->status = "active";
                $teacher->save();
                $result[] = [
                    $teacher
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteTeacher(array $teacherIds, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($teacherId['teacher_id']);
                $teacher->delete();
                $result[] = [
                    $teacher
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateTeacher(array $updateDataList, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataList as $updateData) {
                $teacher = Teacher::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($updateData['id']);
                if ($teacher) {
                    $cleanedData = array_filter($updateData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $teacher->update($cleanedData);
                    }
                }
                $result[] = [
                    $teacher
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getTeachersBySpecialtyPreference(string $specialtyId, object $currentSchool)
    {

        $teachers = TeacherSpecialty::where('specialty_id', $specialtyId)
            ->where('school_branch_id', $currentSchool->id)
            ->with([
                'teacher.specialties',
                'teacher.teacherCoursePreference',
                'teacher.qualifications',
                'teacher.levels',
                'teacher.currentAvatar'
            ])
            ->get();

        if ($teachers->isEmpty()) {
            return ApiResponseService::error('No teachers found for this specialty', null, 404);
        }
        return $teachers->map(function ($teacherPreference) {
            $teacher = $teacherPreference->teacher;
            $numSpecialties = $teacher->specialties->count();
            $numCourses = $teacher->teacherCoursePreference->count();
            return [
                'id' => $teacher->id,
                'school_branch_id' => $teacherPreference->school_branch_id,
                'name' => $teacher->name,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'profile_picture' => $teacher->currentAvatar?->object_key ?? null,
                'username' => $teacher->username ?? null,
                'address' => $teacher->address,
                'qualifications' => $teacher->qualifications ?? null,
                'levels' => $teacher->levels ?? null,
                'num_assigned_courses' => $numCourses,
                'course_assignment_status' => $numCourses > 0 ? 'assigned' : 'unassigned',
                'num_assigned_specialties' => $numSpecialties,
                'specialty_assignment_status' => $numSpecialties > 0 ? 'assigned' : 'unassigned',

            ];
        });
    }
}
