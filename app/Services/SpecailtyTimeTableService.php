<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\Specialty;
use App\Models\InstructorAvailability;
use App\Models\TeacherSpecailtyPreference;
use Carbon\Carbon;

class SpecailtyTimeTableService
{
    // Implement your logic here
    public function deleteTimeTableEntry($currentSchool, $timeTableId)
    {
        $timeTableEntryExists = Timetable::Where('school_id', $currentSchool->id)->find($timeTableId);
        if (!$timeTableEntryExists) {
            return ApiResponseService::error('Entry Not Found', null, 404);
        }
        $timeTableEntryExists->delete();
        return $timeTableEntryExists;
    }
    public function generateTimeTable($specailtyId, $levelId, $currentSchool)
    {

        $timetables = Timetable::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $specailtyId)
            ->where('level_id',  $levelId)
            ->with(['course', 'teacher'])
            ->get();

        if ($timetables->isEmpty()) {
            return ApiResponseService::error("Timetable records seem to be empty", null, 404);
        }
        $timeTable = [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];
        foreach ($timetables as $entry) {
            $day = strtolower($entry->day_of_week);

            if (array_key_exists($day, $timeTable)) {
                $timeTable[$day][] = [
                    "course" => $entry->course->course_title,
                    "start_time" => Carbon::parse($entry->start_time)->format('g:i A'),
                    "end_time" => Carbon::parse($entry->end_time)->format('g:i A'),
                    "teacher" => $entry->teacher->name
                ];
            }
        }

        return $timeTable;
    }

    public function getTimeTableDetails($timeTableId, $currentSchool)
    {
        $timeTableDetails = Timetable::where("school_branch_id", $currentSchool->id)
            ->where("id", $timeTableId)
            ->with(['course', 'teacher'])
            ->get();
        return $timeTableDetails;
    }

    public function getInstructorAvailability($specialtyId, $semesterId, $currentSchool)
    {

        $specialty = Specialty::find($specialtyId);
        if (!$specialty) {
            return response()->json([
                'status' => 'error',
                'message' => 'Specialty not found',
            ], 404);
        }
        $teacherIds = TeacherSpecailtyPreference::where("specialty_id", $specialtyId)->pluck("teacher_id");
        $instructorAvailabilityData = InstructorAvailability::whereIn("teacher_id", $teacherIds)
            ->where("school_branch_id", $currentSchool->id)
            ->where("semester_id", $semesterId)
            ->with(['teacher'])
            ->get();

        $levelId = $specialty->level->id;
        $results = [];
        $timetables = Timetable::whereIn('teacher_id', $teacherIds)
            ->where('semeter_id', $semesterId)
            ->get();
        $timetableData = $timetables->groupBy('teacher_id');
        foreach ($instructorAvailabilityData as $item) {
            $teacherId = $item->teacher_id;
            $day = $item->day_of_week;
            $startTime = $item->start_time;
            $endTime = $item->end_time;
            $availableStartTime = $startTime;
            $availableEndTime = $endTime;
            if (isset($timetableData[$teacherId])) {
                $timetableEntries = $timetableData[$teacherId]->sortBy('start_time');
                foreach ($timetableEntries as $timetable) {
                    if ($timetable->day_of_week === $day) {
                        if ($timetable->start_time < $availableEndTime && $timetable->end_time > $availableStartTime) {
                            if ($timetable->start_time >= $availableStartTime && $timetable->start_time < $availableEndTime) {
                                $availableEndTime = $timetable->start_time;
                                if ($availableStartTime >= $availableEndTime) {
                                    break;
                                }
                            }
                            if ($timetable->end_time > $availableStartTime && $timetable->end_time <= $availableEndTime) {
                                $availableStartTime = $timetable->end_time;
                                if ($availableStartTime >= $availableEndTime) {
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if ($availableStartTime < $availableEndTime) {
                $results[] = [
                    'teacher_id' => $teacherId,
                    'semester_id' => $semesterId,
                    'day' => $day,
                    'available_start_time' => $availableStartTime,
                    'available_end_time' => $availableEndTime,
                    'teacher_name' => $item->teacher->name,
                    'level_id' => $levelId,
                ];
            }
        }

        return $results;
    }
    public function updateTimeTable(array $data, $currentSchool, $timeTableId)
    {
        $timeTableRecord = Timetable::where('school_id', $currentSchool->id)->find($timeTableId);
        if (!$timeTableRecord) {
            return ApiResponseService::error("Time Table Record Not Found", null, 404);
        }
        $clashExists = InstructorAvailability::where('school_id', $currentSchool->id)
            ->where('teacher_id', $data["teacher_id"])
            ->where('level_id', $data["level_id"])
            ->where('semester_id', $data["semester_id"])
            ->where('specialty_id', $data["specialty_id"])
            ->where('day_of_week', $data["day_of_week"])
            ->where(function ($query) use ($data) {
                $query->whereBetween('start_time', [$data["start_time"], $data["end_time"]])
                    ->orWhereBetween('end_time', [$data["start_time"], $data["end_time"]])
                    ->orWhere(function ($query) use ($data) {
                        $query->where('start_time', '<=', $data["start_time"])
                            ->where('end_time', '>=', $data["end_time"]);
                    });
            })
            ->exists();
        if ($clashExists) {
            return ApiResponseService::error("Clashes in the Time Table were detected", null, 400);
        }
        $filteredData = array_filter($data);
        $timeTableRecord->update($filteredData);
        return $timeTableRecord;
    }
}
