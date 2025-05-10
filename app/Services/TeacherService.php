<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Timetable;
use Carbon\Carbon;

class TeacherService
{
    // Implement your logic here
    public function getTeacherDetails($teacher_id)
    {
        $find_teacher = Teacher::findOrFail($teacher_id);
        return $find_teacher;
    }
    public function deletetTeacher($teacher_id)
    {
        $teacher = Teacher::findOrFail($teacher_id);
        $teacher->delete();
        return $teacher;
    }
    public function updateTeacher(array $data, $teacher_id)
    {
        $teacher = Teacher::findOrFail($teacher_id);
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

    public function addSpecailtyPreference(array $specailtyData, $currentSchool, $teacherId)
    {
        $result = [];
        foreach ($specailtyData as $specailty) {
            $createdEntry = TeacherSpecailtyPreference::create([
                'specialty_id' => $specailty["specialty_id"],
                'teacher_id' =>  $teacherId,
                "school_branch_id" => $currentSchool->id
            ]);
            $result[] = $createdEntry;
        }
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
                $teacher = Teacher::findOrFail($teacherId['id']);
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
                $teacher = Teacher::findOrFail($teacherId['id']);
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
                $teacher = Teacher::findOrFail($teacherId['id']);
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

    public function bulkAddTeacherSpecialtyPreference($currentSchool, $preferenceDataList, $teacherIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($teacherIds as $teacherId) {
                foreach ($preferenceDataList as $preferenceData) {
                    $createdEntry = TeacherSpecailtyPreference::create([
                        'specialty_id' => $preferenceData["specialty_id"],
                        'teacher_id' =>  $teacherId,
                        "school_branch_id" => $currentSchool->id
                    ]);
                    $result[] = $createdEntry;
                }
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
