<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\Specialty;
use App\Models\InstructorAvailability;
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
        $instructorAvailabilityData = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->where("semester_id", $semesterId)
            ->with(['teacher'])
            ->get();
        $levelId = $specialty->level->id;
        $results = $instructorAvailabilityData->map(function ($item) use ($semesterId, $levelId) {
            return [
                'teacher_id' => $item->teacher_id,
                'semester_id' => $semesterId,
                'day' => $item->day_of_week,
                'start_time' => $item->start_time,
                'teacher_name' => $item->teacher->name,
                'end_time' => $item->end_time,
                'level_id' => $levelId,
            ];
        })->toArray();

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
