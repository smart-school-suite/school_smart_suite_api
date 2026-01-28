<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\SemesterTimetable\SemesterTimetableSlot;

class SemesterTimetableVersionService
{
    public function getTimetableSlotsVersionId(string $semesterId, string $versionId, object $currentSchool)
    {
        $timetableVersion = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("school_semester_id", $semesterId)
            ->with(['semesterActiveTimetable', 'timeTableSlot.course.types', 'timeTableSlot.teacher', 'timeTableSlot.hall.types'])
            ->find($versionId);

        if (!$timetableVersion) {
            throw new AppException(
                "Timetable version not found.",
                404,
                "Timetable Version Not Found",
                "The timetable version you are trying to access does not exist or has been deleted."
            );
        }

        $slots = $timetableVersion->timeTableSlot;

        $daysMap = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            0 => 'sunday',
        ];

        $grouped = $slots->groupBy('day_of_week');

        $timetable = [];

        foreach ($grouped as $dayNumber => $daySlots) {
            if ($daySlots->isEmpty()) {
                continue;
            }

            $dayName = $daysMap[$dayNumber] ?? null;

            if (!$dayName) {
                continue;
            }

            $sortedSlots = $daySlots->sortBy('start_time')->values();

            $formatted = $sortedSlots->map(function ($slot) {
                return [
                    'id'              => $slot->id,
                    'course_id'       => $slot->course_id,
                    'course_name'     => $slot->course?->name ?? null,
                    'course_code'     => $slot->course?->code ?? null,
                    'course_credit'   => $slot->course?->credit ?? null,
                    'course_types'    => $slot->course?->types->pluck('name')->toArray() ?? null,
                    'teacher_id'      => $slot->teacher_id,
                    'teacher_name'    => $slot->teacher?->name ?? null,
                    'hall_id'         => $slot->hall_id,
                    'hall_name'       => $slot->hall?->name ?? null,
                    'hall_types'      => $slot->hall?->types->pluck('name')->toArray() ?? null,
                    'start_time'      => $slot->start_time,
                    'end_time'        => $slot->end_time,
                    'break'           => $slot->break,
                    'student_batch_id' => $slot->student_batch_id,
                ];
            })->all();

            $timetable[$dayName] = $formatted;
        }

        return [
            'version_id'     => $timetableVersion->id,
            'version_name'   => $timetableVersion->name ?? null,
            'is_active'      => $timetableVersion->semesterActiveTimetable !== null,
            'timetable'      => $timetable,
            'days_with_slots' => array_keys($timetable),
        ];
    }
    public function deleteTimetableVersion(string $versionId, string $semesterId, object $currentSchool)
    {
        $version = SemesterTimetableVersion::where('id', $versionId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->firstOrFail();

        $version->delete();
        return $version;
    }
    public function deleteTimetableVersionSlot(string $slotId, object $currentSchool)
    {
        $slot = SemesterTimetableSlot::where('id', $slotId)
            ->where('school_branch_id', $currentSchool->id)
            ->first();
        if (!$slot) {
            throw new AppException(
                "Timetable slot not found.",
                404,
                "Timetable Slot Not Found",
                "The timetable slot you are trying to delete does not exist or has been deleted."
            );
        }
        $slot->delete();
        return $slot;
    }
    public function getTimetableVersionSlotDetail(string $slotId, object $currentSchool)
    {
        $slot = SemesterTimetableSlot::where('id', $slotId)
            ->where('school_branch_id', $currentSchool->id)
            ->with(['course.types', 'teacher', 'hall.types'])
            ->first();
        if (!$slot) {
            throw new AppException(
                "Timetable slot not found.",
                404,
                "Timetable Slot Not Found",
                "The timetable slot you are trying to access does not exist or has been deleted."
            );
        }
        return $slot;
    }
}
