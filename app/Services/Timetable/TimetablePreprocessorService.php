<?php

namespace App\Services\Timetable;

use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use App\Models\TeacherSpecailtyPreference;

class TimetablePreprocessorService
{
    public function preprocess($currentSchool, $schoolSemesterId, array $data): array
    {
        $messages = [];

        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'semester'])
            ->find($schoolSemesterId);

        if (!$schoolSemester) {
            throw new AppException(
                "School semester with ID '{$schoolSemesterId}' not found for school branch '{$currentSchool->id}'.",
                404,
                "School Semester Not Found 📅",
                "The specified school semester does not exist. Please verify that the semester has not been deleted and try again.",
                null
            );
        }
        $specialtyId = $schoolSemester->specialty_id;
        $semesterId = $schoolSemester->semester_id;
        $specialtyName = $schoolSemester->specialty->specialty_name ?? 'Unknown Specialty';

        $teachers = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->with('teacher')
            ->get();

        if ($teachers->isEmpty()) {
            throw new AppException(
                "No teacher preferences found for specialty ID '{$specialtyId}' at school branch '{$currentSchool->id}'.",
                404,
                "No Teachers Assigned to Specialty 🧑‍🏫",
                "We couldn't find any teachers who have set a preference to teach the specialty: '{$specialtyName}'. Please assign relevant preferences to teachers before proceeding.",
                null
            );
        }

        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->where("semester_id", $semesterId)
            ->get();

        if ($courses->isEmpty()) {
            throw new AppException(
                "No courses found for specialty ID '{$specialtyId}' and semester ID '{$semesterId}' at school branch '{$currentSchool->id}'.",
                404,
                "No Courses Defined for Semester 📚",
                "There are no courses currently defined for the specialty: '{$specialtyName}' in the '{$schoolSemester->semester->name}'. Please ensure courses are properly configured.",
                null
            );
        }

        $teacherSlots = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teachers->pluck('teacher_id'))
            ->get()
            ->groupBy('teacher_id');

        $allPossibleSlots = $this->buildAllPossibleSlots($data);

        $teacherAvailability = $this->mapTeacherAvailability($teachers, $teacherSlots, $allPossibleSlots, $data);
        $totalAvailableSlots = collect($teacherAvailability)->flatten(1)->count();

        $numTeachers = $teachers->count();
        $numDays = count($data['days']);
        $minRequiredSlots = $numTeachers * $data['min_day_slots'] * $numDays;
        $maxPossibleAssignments = min(
            $courses->count() * $data['max_week_sessions'],
            $numTeachers * $data['max_week_slots']
        );

        if ($minRequiredSlots > $totalAvailableSlots) {
            $originalMinDaySlots = $data['min_day_slots'];
            $data['min_day_slots'] = max(1, floor($totalAvailableSlots / ($numTeachers * $numDays)));
            $messages[] = "Adjusted Minimum Daily Slots from {$originalMinDaySlots} to {$data['min_day_slots']} because the total available slots ({$totalAvailableSlots}) are insufficient to meet the minimum requirement of {$minRequiredSlots} slots across {$numTeachers} teachers and {$numDays} days.";
        }

        $avgAvailabilityPerTeacher = $totalAvailableSlots / $numTeachers;
        if ($data['max_week_slots'] > $avgAvailabilityPerTeacher) {
            $originalMaxWeekSlots = $data['max_week_slots'];
            $data['max_week_slots'] = floor($avgAvailabilityPerTeacher);
            $messages[] = "Adjusted Maximum Weekly Slots from {$originalMaxWeekSlots} to {$data['max_week_slots']} based on average teacher availability ({$avgAvailabilityPerTeacher} slots per teacher).";
        }

        if ($courses->count() * $data['max_week_sessions'] > $totalAvailableSlots) {
            $originalMaxWeekSessions = $data['max_week_sessions'];
            $data['max_week_sessions'] = max(1, floor($totalAvailableSlots / $courses->count()));
            $messages[] = "Adjusted Max weekly sessions from {$originalMaxWeekSessions} to {$data['max_week_sessions']} because the total available slots ({$totalAvailableSlots}) cannot support {$courses->count()} courses each with {$originalMaxWeekSessions} sessions.";
        }

        $maxCoursesPerDayPossible = floor($totalAvailableSlots / ($numDays * $data['max_week_sessions']));
        if ($data['max_courses_day'] > $maxCoursesPerDayPossible) {
            $originalMaxCoursesDay = $data['max_courses_day'];
            $data['max_courses_day'] = $maxCoursesPerDayPossible;
            $messages[] = "Adjusted max courses day from {$originalMaxCoursesDay} to {$data['max_courses_day']} to align with available slots and weekly session limits.";
        }

        if ($data['consecutive_limit'] > $data['max_day_slots']) {
            $originalConsecutiveLimit = $data['consecutive_limit'];
            $data['consecutive_limit'] = $data['max_day_slots'];
            $messages[] = "Adjusted consecutive limit from {$originalConsecutiveLimit} to {$data['consecutive_limit']} as it cannot exceed max_day_slots.";
        }

        if ($data['min_gap'] > 0) {
            $effectiveSlots = $totalAvailableSlots * (1 - $data['min_gap'] / 10);
            if ($minRequiredSlots > $effectiveSlots) {
                $originalMinGap = $data['min_gap'];
                $data['min_gap'] = 0;
                $messages[] = "Disabled Minimum Course Gap (from {$originalMinGap} to 0) because it would reduce effective slots below the minimum required ({$minRequiredSlots}).";
            }
        }

        if ($minRequiredSlots > $totalAvailableSlots) {
            throw new \RuntimeException("Constraints remain unrealistic even after adjustments: Required {$minRequiredSlots} slots, but only {$totalAvailableSlots} available.");
        }

        $teacherBusyness = collect();
        foreach ($teachers as $teacher) {
            $busySlots = $teacherSlots->get($teacher->teacher_id, collect());
            $totalBusyMinutes = $busySlots->sum(function ($slot) {
                return (strtotime($slot->end_time) - strtotime($slot->start_time)) / 60;
            });
            $teacherBusyness->push([
                'teacher_id' => $teacher->teacher_id,
                'busyness' => $totalBusyMinutes,
            ]);
        }

        $sortedTeachers = $teacherBusyness->sortBy('busyness')->pluck('teacher_id')->toArray();

        if (empty($messages)) {
            $messages[] = "All constraints are realistic no adjustments needed.";
        }

        return [
            'adjusted_data' => $data,
            'messages' => $messages,
            'teacher_priority_order' => $sortedTeachers,
        ];
    }

    protected function buildAllPossibleSlots(array $data): Collection
    {
        $slots = collect();
        $minSlotLength = (int) $data['min_slot_length'];
        $maxSlotLength = (int) $data['max_slot_length'];
        $slotIncrement = (int) $data['slot_increment'];

        $slotLengths = [];
        for ($length = $minSlotLength; $length <= $maxSlotLength; $length += $slotIncrement) {
            $slotLengths[] = $length;
        }

        foreach ($data['days'] as $day) {
            $start = strtotime($data['start']);
            $end = strtotime($data['end']);
            $current = $start;

            while ($current < $end) {
                foreach ($slotLengths as $length) {
                    $slotEnd = $current + ($length * 60);
                    if ($slotEnd <= $end) {
                        $slots->push([
                            'day' => $day,
                            'start' => date('H:i', $current),
                            'end' => date('H:i', $slotEnd),
                            'duration' => $length,
                            'key' => "{$day}-" . date('H:i', $current) . "-{$length}",
                            'timestamp' => $current,
                        ]);
                    }
                }
                $current += $minSlotLength * 60;
            }
        }

        return $slots->sortBy(['day', 'timestamp'])->values();
    }

    protected function mapTeacherAvailability($teachers, $teacherSlots, Collection $availableSlots, $data): array
    {
        $map = [];
        $allBusy = $teacherSlots->flatten();

        foreach ($teachers as $teacher) {
            $available = $availableSlots->sortBy(function ($slot) use ($data) {
                $dayOrder = array_flip($data['days'])[$slot['day']] ?? 99;
                return $dayOrder * 10000 + $slot['timestamp'];
            })->values();

            $map[$teacher->teacher_id] = $available->reject(function ($slot) use ($allBusy) {
                return $allBusy->contains(function ($b) use ($slot) {
                    $b_start = strtotime($b->start_time);
                    $b_end = strtotime($b->end_time);
                    $slot_start = $slot['timestamp'];
                    $slot_end = $slot['timestamp'] + ($slot['duration'] * 60);
                    return $b->day === $slot['day'] &&
                        $slot_start < $b_end &&
                        $slot_end > $b_start;
                });
            })->values();
        }

        return $map;
    }
}
