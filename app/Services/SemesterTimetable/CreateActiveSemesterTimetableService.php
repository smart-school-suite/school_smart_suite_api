<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SemesterTimetable\SemesterActiveTimetable;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SemesterTimetable\SemesterTimetableVersion;

class CreateActiveSemesterTimetableService
{
    public function createActiveSemesterTimetable(string $timetableVersionId, object $currentSchool)
    {
        $timetableVersions = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->get();

        $timetableVersion = $timetableVersions->where("id", $timetableVersionId)->first();
        if (!$timetableVersion) {
            throw new AppException(
                "Semester Timetable Version not found",
                404,
                "Timetable Version Not Found",
                "Timetable version not found please ensure it exist and has not been deleted"
            );
        }

        $slots = SemesterTimetableSlot::where("timetable_version_id", $timetableVersionId)
            ->where("school_branch_id", $currentSchool->id)
            ->get();

        $resources = [
            'hall' => ['hall_id', 'day_of_week'],
            'teacher' => ['teacher_id', 'day_of_week'],
            'student' => ['student_batch_id', 'day_of_week'],
        ];

        foreach ($resources as $resourceName => $groupByFields) {
            if ($this->hasOverlaps($slots, $groupByFields)) {
                throw new AppException(
                    "Overlaps detected in {$resourceName} scheduling",
                    400,
                    "Scheduling Conflict",
                    "The timetable version contains overlapping slots for {$resourceName}s. Please resolve before activating."
                );
            }
        }

        $activeTimetable = SemesterActiveTimetable::where("school_branch_id", $currentSchool->id)
            ->where("school_semester_id", $timetableVersion->draft->school_semester_id)
            ->first();

        if (!$activeTimetable) {
          $activeSemesterTimetable =  SemesterActiveTimetable::create([
                "school_semester_id" => $timetableVersion->draft->school_semester_id,
                "timetable_version_id" => $timetableVersion->id,
                "school_branch_id" => $currentSchool->id
            ]);
           return $activeSemesterTimetable;
        } else {
            $activeTimetable->update([
                "timetable_version_id" => $timetableVersion->id
            ]);
            return $activeTimetable;
        }
    }
    private function hasOverlaps($slots, $groupByFields)
    {
        $grouped = $slots->groupBy($groupByFields);

        foreach ($grouped as $group) {
            if ($group->count() < 2) {
                continue;
            }

            $sorted = $group->sortBy('start_time');

            $sortedArray = $sorted->values();

            for ($i = 0; $i < count($sortedArray) - 1; $i++) {
                $current = $sortedArray[$i];
                $next = $sortedArray[$i + 1];

                if ($current->end_time > $next->start_time) {
                    return true;
                }
            }
        }

        return false;
    }
}
