<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use Carbon\Carbon;

class SemesterTimetableVersionService
{
    public function getVersionSchoolSemesterId(object $currentSchool, string $schoolSemesterId)
    {
        $versions = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("school_semester_id", $schoolSemesterId)
            ->orderBy('created_at', 'desc')
            ->get();

        $latestId = $versions->first()?->id;

        return $versions->map(function ($version) use ($latestId) {
            return [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'label' => $version->label,
                'scheduler_status' => $version->scheduler_status,
                'is_latest' => $version->id === $latestId,
                'created_at' => $version->created_at,
                'updated_at' => $version->updated_at,
            ];
        });
    }
    public function createVersion(object $currentSchool, array $data)
    {
        $latest = SemesterTimetableVersion::where([
            'school_branch_id'   => $currentSchool->id,
            'school_semester_id' => $data['school_semester_id'],
        ])
            ->max('version_number');

        $nextNumber = ($latest ?? 0) + 1;

        return SemesterTimetableVersion::create([
            'version_number'     => $nextNumber,
            'label'              => "Version $nextNumber",
            'school_branch_id'   => $currentSchool->id,
            'school_semester_id' => $data['school_semester_id'],
        ]);
    }

    public function deleteVersion(object $currentSchool, string $versionId)
    {
        $version = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("id", $versionId)
            ->first();
        if (!$version) {
            throw new AppException(
                "Timetable Version Not Found",
                404,
                "Not Found",
                "The Timetable Version Your Trying to delete was not found please ensure that it has not been deleted and try again"
            );
        }

        $version->delete();
        return $version;
    }

    public function getTimetableSlotsVersionId(string $versionId, object $currentSchool)
    {
        $timetableVersion = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->with([
                'semesterActiveTimetable',
                'timeTableSlot.course.types',
                'timeTableSlot.teacher',
                'timeTableSlot.hall.types'
            ])
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

        $formattedSlots = $slots
            ->groupBy('day')
            ->map(function ($daySlots, $day) {
                return [
                    'day' => $day,
                    'slots' => $daySlots
                        ->sortBy(fn($slot) => $slot->start_time ? Carbon::parse($slot->start_time)->timestamp : PHP_INT_MAX)
                        ->values()
                        ->map(function ($slot) {
                            return [
                                'id' => $slot->id ?? null,
                                'day' => $slot->day ?? null,

                                'start_time' => $slot->start_time ? Carbon::parse($slot->start_time)->format('H:i') : null,
                                'end_time' => $slot->end_time ? Carbon::parse($slot->end_time)->format('H:i') : null,
                                'break' => (bool) ($slot->break ?? false),

                                'teacher_id' => $slot->teacher_id ?? null,
                                'teacher_name' => $slot->teacher?->name ?? null,
                                'teacher_avatar' => $slot->teacher?->profile_picture ?? null,

                                'course_id' => $slot->course_id ?? null,
                                'course_title' => $slot->course?->course_title ?? null,
                                'course_code' => $slot->course?->course_code ?? null,
                                'course_types' => $slot->course?->types->pluck('name')->toArray() ?? [],

                                'hall_id' => $slot->hall_id ?? null,
                                'hall_name' => $slot->hall?->name ?? null,
                                'hall_types' => $slot->hall?->types->pluck('name')->toArray() ?? [],
                                'hall_location' => $slot->hall?->location ?? null,
                            ];
                        }),
                ];
            })
            ->values();

        return [
            'version' => $timetableVersion->only(['id', 'version_number', 'label', 'scheduler_status']),
            'timetable' => $formattedSlots,
        ];
    }
}
