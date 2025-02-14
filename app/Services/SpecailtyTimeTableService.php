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
    public function generateTimeTable(array $routeParams, $currentSchool)
    {

        $timetables = Timetable::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $routeParams['specailty_id'])
            ->where('level_id',  $routeParams['level_id'])
            ->where('semester_id', $routeParams['semester_id'])
            ->where("student_batch_id", $routeParams['student_batch_id'])
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

    public function getInstructorAvailability(string $specialtyId, string $semesterId, object $currentSchool)
    {
           $specialty = Specialty::with(['level'])->find($specialtyId);

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
        $timetables = Timetable::whereIn('teacher_id', $teacherIds)
            ->where('semester_id', $semesterId)
            ->get();

        $timetableData = $timetables->groupBy('teacher_id');

        $results = [];

        foreach ($instructorAvailabilityData as $availability) {
            $teacherId = $availability->teacher_id;
            $day = $availability->day_of_week;
            $startTime = Carbon::parse($availability->start_time); // Use Carbon for easier time manipulation
            $endTime = Carbon::parse($availability->end_time);

            $availableSlots = [[$startTime, $endTime]]; // Initialize with the full availability

            if (isset($timetableData[$teacherId])) {
                $timetableEntries = $timetableData[$teacherId]->where('day_of_week', $day)->sortBy('start_time');

                foreach ($timetableEntries as $timetable) {
                    $timetableStartTime = Carbon::parse($timetable->start_time);
                    $timetableEndTime = Carbon::parse($timetable->end_time);

                    $newSlots = [];
                    foreach ($availableSlots as [$slotStart, $slotEnd]) {
                        if ($timetableEndTime <= $slotStart || $timetableStartTime >= $slotEnd) {
                            $newSlots[] = [$slotStart, $slotEnd];
                            continue;
                        }

                        if ($timetableStartTime <= $slotStart && $timetableEndTime >= $slotEnd) {
                            continue;
                        }

                        if ($timetableStartTime <= $slotStart && $timetableEndTime < $slotEnd) {
                            $newSlots[] = [$timetableEndTime, $slotEnd];
                            continue;
                        }

                        if ($timetableStartTime > $slotStart && $timetableEndTime >= $slotEnd) {
                            $newSlots[] = [$slotStart, $timetableStartTime];
                            continue;
                        }

                        $newSlots[] = [$slotStart, $timetableStartTime];
                        $newSlots[] = [$timetableEndTime, $slotEnd];
                    }
                    $availableSlots = $newSlots;
                }
            }

            foreach ($availableSlots as [$availableStartTime, $availableEndTime]) {
                $results[] = [
                    'teacher_id' => $teacherId,
                    'semester_id' => $semesterId,
                    'day' => $day,
                    'available_start_time' => $availableStartTime->format('g:i A'),
                    'available_end_time' => $availableEndTime->format('g:i A'),
                    'teacher_name' => $availability->teacher->name,
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
