<?php

namespace App\Services;

use App\Models\Specialty;
use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use App\Notifications\SpecialtyAssignedToTeacher;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Timetable;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Throwable;

class TeacherService
{
    // Implement your logic here
    public function getTeacherDetails($teacherId)
    {
        $find_teacher = Teacher::findOrFail($teacherId);
        return $find_teacher;
    }
    public function deletetTeacher($teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $teacher->delete();
        return $teacher;
    }
    public function updateTeacher(array $data, $teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $filterData = array_filter($data);
        $teacher->update($filterData);
        return $teacher;
    }
    public function getTeacherSchedule($teacherId, $currentSchool)
    {
        $teacher_timetable_data = Timetable::where('school_branch_id', $currentSchool->id)
            ->where('teacher_id', $teacherId)
            ->with(['specialty', 'course', 'level'])
            ->get();

        if ($teacher_timetable_data->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No records found'
            ], 409);
        }

        $time_table = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];

        foreach ($teacher_timetable_data as $entry) {
            $day = strtolower($entry->day_of_week);

            if (array_key_exists($day, $time_table)) {
                $time_table[$day][] = [
                    'level_name' => $entry->level->name,
                    'level' => $entry->level->level,
                    "specialty" => $entry->specialty->specialty_name,
                    "course" => $entry->course->course_title,
                    "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
                    "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
                    "teacher" => $entry->teacher->name
                ];
            }
        }

        return $time_table;
    }
    public function getAllTeachers($currentSchool)
    {
        $getInstructors = Teacher::where("school_branch_id", $currentSchool->id)
            ->get();
        return $getInstructors;
    }
    public function addSpecailtyPreference(array $preferenceData, $currentSchool)
    {
        $result = [];
        $teacher = null;
        $specialties = [];
        $specialty = null;
        foreach ($preferenceData as $preference) {
            if($specialty == null){
                $specialtyDetails = Specialty::where("school_branch_id", $currentSchool->id)
                                   ->with('level')
                                   ->find($preference['specialty_id']);
                $specialty = $specialtyDetails;
            }
            if($teacher == null){
                $teacherDetails = Teacher::where("school_branch_id", $currentSchool->id)
                                         ->find($preference['teacher_id']);
                $teacher = $teacherDetails;
            }
            $createdEntry = TeacherSpecailtyPreference::create([
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
    public function deactivateTeacher($teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $teacher->status = "inactive";
        $teacher->save();
        return $teacher;
    }
    public function activateTeacher($teacherId)
    {
        $teacher = Teacher::findOrFail($teacherId);
        $teacher->status = "active";
        $teacher->save();
        return $teacher;
    }
    public function bulkDeactivateTeacher($teacherIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId['teacher_id']);
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
    public function bulkActivateTeacher($teacherIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId['teacher_id']);
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
    public function bulkDeleteTeacher($teacherIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                $teacher = Teacher::findOrFail($teacherId['teacher_id']);
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
    public function bulkUpdateTeacher($updateDataList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataList as $updateData) {
                $teacher = Teacher::findOrFail($updateData['id']);
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
    public function uploadProfilePicture($request, $authTeacher)
    {

        try {
             $teacher = Teacher::findOrFail($authTeacher->id);
            DB::transaction(function () use ($request, $teacher) {

                if ($teacher->profile_picture) {
                    Storage::disk('public')->delete('TeacherAvatars/' . $teacher->profile_picture);
                }
                $profilePicture = $request->file('profile_picture');
                $fileName = time() . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->storeAs('public/TeacherAvatars', $fileName);

                $teacher->profile_picture = $fileName;
                $teacher->save();
            });
            return true;
        } catch (Throwable $e) {
            throw $e;
        }
    }
    public function deleteProfilePicture($authTeacher)
    {
        try {
            $teacher = Teacher::findOrFail($authTeacher->id);
            if (!$teacher->profile_picture) {
                return ApiResponseService::error("No Profile Picture to Delete {$teacher->name}", null, 400);
            }
            Storage::disk('public')->delete('TeacherAvatars/' . $teacher->profile_picture);

            $teacher->profile_picture = null;
            $teacher->save();

            return $teacher;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getTeachersBySpecialtyPreference($specialtyId, $currentSchool){
        $teachers = TeacherSpecailtyPreference::where('specialty_id', $specialtyId)
            ->where('school_branch_id', $currentSchool->id)
            ->with('teacher')
            ->get();

        if ($teachers->isEmpty()) {
            return ApiResponseService::error('No teachers found for this specialty', null, 404);
        }

        return $teachers->pluck('teacher');
    }

}
