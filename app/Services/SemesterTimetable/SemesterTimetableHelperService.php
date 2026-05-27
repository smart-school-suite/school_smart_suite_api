<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\InstructorAvailability;
use App\Models\PeriodDuration\PeriodDuration;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SpecialtyHall;
use App\Models\TeacherSpecailtyPreference;
use Carbon\Carbon;

class SemesterTimetableHelperService
{
    //teacher methods
    public function getAvailableTeachersFixed(object $currentSchool, array $params): array
    {
        $startTime = Carbon::parse($params['start_time']);
        $endTime = Carbon::parse($params['end_time']);
        $day = $params['day'];
        $schoolSemesterId = $params['school_semester_id'];

        if (!$schoolSemesterId) {
            throw new AppException(
                "missing_semester_id",
                400,
                "Semester Required",
                "School semester ID is required to check teacher availability."
            );
        }

        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty'])
            ->find($schoolSemesterId);

        if (!$schoolSemester) {
            throw new AppException(
                "school_semester_not_found",
                404,
                "Semester Not Found",
                "The specified school semester could not be found."
            );
        }

        $teachers = TeacherSpecailtyPreference::where("specialty_id", $schoolSemester->specialty_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        if ($teachers->isEmpty()) {
            return [
                "recommended" => [],
                "not_recommended" => [],
                "summary" => [
                    "total_teachers" => 0,
                    "available" => 0,
                    "busy" => 0
                ]
            ];
        }

        $teacherIds = $teachers->pluck('id')->toArray();

        $busyTeacherIds = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teacherIds)
            ->where("day_of_week", $day)
            ->where("school_semester_id", $schoolSemesterId)
            ->where(function ($query) use ($startTime, $endTime) {
                $startTimeStr = $startTime->format('H:i:s');
                $endTimeStr = $endTime->format('H:i:s');

                $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
                    $q->where('start_time', '<', $endTimeStr)
                        ->where('end_time', '>', $startTimeStr);
                });
            })
            ->whereHas('semesterTimetableVersion', function ($query) {
                $query->whereHas('semesterActiveTimetable');
            })
            ->distinct()
            ->pluck('teacher_id')
            ->toArray();

        $recommended = [];
        $notRecommended = [];

        foreach ($teachers as $teacher) {
            $teacherData = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'profile_picture' => $teacher->profile_picture,
            ];

            if (in_array($teacher->id, $busyTeacherIds)) {
                $notRecommended[] = array_merge($teacherData, [
                    'reason' => 'Teacher is busy during the requested time slot',
                    'availability' => 'busy'
                ]);
            } else {
                $recommended[] = array_merge($teacherData, [
                    'availability' => 'available'
                ]);
            }
        }

        return [
            "recommended" => $recommended,
            "not_recommended" => $notRecommended,
            "summary" => [
                "total_teachers" => $teachers->count(),
                "available" => count($recommended),
                "busy" => count($notRecommended),
                "requested_slot" => [
                    "day" => $day,
                    "start_time" => $startTime->format('H:i:s'),
                    "end_time" => $endTime->format('H:i:s')
                ]
            ]
        ];
    }
    public function getAvailableTeachersPref(object $currentSchool, array $params): array
    {
        $startTime = Carbon::parse($params['start_time']);
        $endTime = Carbon::parse($params['end_time']);
        $day = $params['day'];
        $schoolSemesterId = $params['school_semester_id'];

        if (!$schoolSemesterId) {
            throw new AppException(
                "missing_semester_id",
                400,
                "Semester Required",
                "School semester ID is required to check teacher availability."
            );
        }

        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty'])
            ->find($schoolSemesterId);

        if (!$schoolSemester) {
            throw new AppException(
                "school_semester_not_found",
                404,
                "Semester Not Found",
                "The specified school semester could not be found."
            );
        }

        $teachers = TeacherSpecailtyPreference::where("specialty_id", $schoolSemester->specialty_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        if ($teachers->isEmpty()) {
            return [
                "recommended" => [],
                "not_recommended" => [],
                "summary" => [
                    "total_teachers" => 0,
                    "available" => 0,
                    "busy" => 0
                ]
            ];
        }

        $teacherIds = $teachers->pluck('id')->toArray();

        $busyTeacherIds = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teacherIds)
            ->where("day", $day)
            ->where("school_semester_id", $schoolSemesterId)
            ->whereHas('semesterTimetableVersion', function ($query) {
                $query->whereHas('semesterActiveTimetable');
            })
            // ->with(['schoolSemester', function ($query) {
            //      $query->where("end_date", '>=', now());
            // }])
            ->where(function ($query) use ($startTime, $endTime) {
                $startTimeStr = $startTime->format('H:i:s');
                $endTimeStr = $endTime->format('H:i:s');
                $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
                    $q->where('start_time', '<', $endTimeStr)
                        ->where('end_time', '>', $startTimeStr);
                });
            })
            ->distinct()
            ->pluck('teacher_id')
            ->toArray();

        $instructorAvailabilities = InstructorAvailability::where("school_branch_id", $currentSchool->id)
            ->whereIn('teacher_id', $teacherIds)
            ->where('school_semester_id', $schoolSemesterId)
            ->with(['instructorAvailabilitySlot'])
            ->get()
            ->keyBy('teacher_id');

        $recommended = [];
        $notRecommended = [];

        foreach ($teachers as $teacher) {
            $teacherData = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'profile_picture' => $teacher->profile_picture,
            ];

            if (in_array($teacher->id, $busyTeacherIds)) {
                $notRecommended[] = $teacherData;
                continue;
            }

            $isAvailable = true;

            if ($instructorAvailabilities->has($teacher->id)) {
                $availability = $instructorAvailabilities->get($teacher->id);
                $availabilitySlot = $availability->instructorAvailabilitySlot;

                if ($availabilitySlot) {
                    $slotForDay = $availabilitySlot->where('day_of_week', $day)->first();

                    if (!$slotForDay) {
                        $isAvailable = false;
                    } else {
                        $slotStart = Carbon::parse($slotForDay->start_time);
                        $slotEnd = Carbon::parse($slotForDay->end_time);
                        $requestStart = Carbon::parse($startTime->format('H:i:s'));
                        $requestEnd = Carbon::parse($endTime->format('H:i:s'));

                        if ($requestStart < $slotStart || $requestEnd > $slotEnd) {
                            $isAvailable = false;
                        }
                    }
                }
            }

            if ($isAvailable) {
                $recommended[] = $teacherData;
            } else {
                $notRecommended[] = $teacherData;
            }
        }

        return [
            "recommended" => $recommended,
            "not_recommended" => $notRecommended,
            "summary" => [
                "total_teachers" => $teachers->count(),
                "available" => count($recommended),
                "busy" => count($notRecommended)
            ]
        ];
    }
    public function getTeachers(object $currentSchool, string $schoolSemesterId)
    {
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty'])
            ->find($schoolSemesterId);

        if (!$schoolSemester) {
            throw new AppException(
                "school_semester_not_found",
                404,
                "Semester Not Found",
                "The specified school semester could not be found."
            );
        }

        $teachers = TeacherSpecailtyPreference::where("specialty_id", $schoolSemester->specialty_id)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        return $teachers->map(fn($teacher) => [
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'profile_picture' => $teacher->profile_picture
        ]);
    }

    //slots
    public function generateSlots(array $params): array
    {
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $day = $params['day'];
        $periodDurationId = $params['period_duration_id'];

        if (!$periodDurationId) {
            throw new AppException(
                "missing_period_duration",
                400,
                "Period Duration Required",
                "Period duration ID is required to generate time slots."
            );
        }

        $periodDuration = PeriodDuration::find($periodDurationId);

        if (!$periodDuration) {
            throw new AppException(
                "period_duration_not_found",
                404,
                "Period Duration Not Found",
                "The specified period duration could not be found."
            );
        }

        $minutes = $periodDuration->minutes;

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($start >= $end) {
            throw new AppException(
                "invalid_time_range",
                400,
                "Invalid Time Range",
                "Start time must be before end time."
            );
        }

        $slots = [];
        $currentStart = clone $start;

        while ($currentStart->copy()->addMinutes($minutes) <= $end) {
            $slotEnd = clone $currentStart;
            $slotEnd->addMinutes($minutes);

            $slots[] = [
                'start_time' => $currentStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'day' => $day,
                'duration_minutes' => $minutes,
                'period_duration_id' => $periodDurationId,
                'period_duration_name' => $periodDuration->name
            ];

            $currentStart->addMinutes($minutes);
        }

        if (empty($slots)) {
            throw new AppException(
                "no_slots_generated",
                422,
                "No Slots Generated",
                "Unable to generate any time slots within the provided time range."
            );
        }

        return [
            'slots' => $slots,
            'summary' => [
                'total_slots' => count($slots),
                'duration_minutes' => $minutes,
                'time_range' => [
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i')
                ],
                'day' => $day
            ]
        ];
    }

    //halls
    public function getAvailableHalls(object $currentSchool, array $params): array
    {
        $startTime = Carbon::parse($params['start_time']);
        $endTime = Carbon::parse($params['end_time']);
        $day = $params['day'];
        $schoolSemesterId = $params['school_semester_id'];

        if (!$schoolSemesterId) {
            throw new AppException(
                "missing_semester_id",
                400,
                "Semester Required",
                "School semester ID is required to check hall availability."
            );
        }

        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty'])
            ->find($schoolSemesterId);

        if (!$schoolSemester) {
            throw new AppException(
                "school_semester_not_found",
                404,
                "Semester Not Found",
                "The specified school semester could not be found."
            );
        }

        $specialtyHalls = SpecialtyHall::where('school_branch_id', $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->with(['hall'])
            ->get()
            ->pluck('hall')
            ->filter()
            ->unique('id')
            ->values();

        if ($specialtyHalls->isEmpty()) {
            return [
                "recommended" => [],
                "not_recommended" => [],
                "summary" => [
                    "total_halls" => 0,
                    "available" => 0,
                    "busy" => 0
                ]
            ];
        }

        $hallIds = $specialtyHalls->pluck('id')->toArray();
        $startTimeStr = $startTime->format('H:i:s');
        $endTimeStr = $endTime->format('H:i:s');

        $busyHallIds = SemesterTimetableSlot::where("school_branch_id", $currentSchool->id)
            ->whereIn("hall_id", $hallIds)
            ->where("day_of_week", $day)
            ->where("school_semester_id", $schoolSemesterId)
            ->where(function ($query) use ($startTimeStr, $endTimeStr) {
                $query->where(function ($q) use ($startTimeStr, $endTimeStr) {
                    $q->where('start_time', '<', $endTimeStr)
                        ->where('end_time', '>', $startTimeStr);
                });
            })
            ->whereHas('semesterTimetableVersion', function ($query) {
                $query->whereHas('semesterActiveTimetable');
            })
            ->distinct()
            ->pluck('hall_id')
            ->toArray();

        $recommended = [];
        $notRecommended = [];

        foreach ($specialtyHalls as $hall) {
            $hallData = [
                'id' => $hall->id,
                'name' => $hall->name,
                'capacity' => $hall->capacity,
                'location' => $hall->location ?? null,
            ];

            if (in_array($hall->id, $busyHallIds)) {
                $notRecommended[] = $hallData;
            } else {
                $recommended[] = $hallData;
            }
        }

        return [
            "recommended" => $recommended,
            "not_recommended" => $notRecommended,
            "summary" => [
                "total_halls" => $specialtyHalls->count(),
                "available" => count($recommended),
                "busy" => count($notRecommended),
                "requested_slot" => [
                    "day" => $day,
                    "start_time" => $startTime->format('H:i:s'),
                    "end_time" => $endTime->format('H:i:s')
                ]
            ]
        ];
    }
}
