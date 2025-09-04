<?php

namespace App\Services;
use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\TeacherSpecailtyPreference;
class AutoGenTimetablePreprocessorService
{
    // Implement your logic here
    /**
     * Preprocess the configuration to ensure constraints are realistic.
     * Adjusts unrealistic constraints based on teacher availabilities, courses, and slots.
     * Returns the adjusted configuration and a list of adjustment messages.
     *
     * @param mixed $currentSchool The current school object
     * @param string $schoolSemesterId The ID of the school semester
     * @param array $data Original configuration data
     * @return array ['adjusted_data' => array, 'messages' => array]
     */
    public function preprocess($currentSchool, $schoolSemesterId, array $data): array
    {
        $messages = [];

        // Load semester context
        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolSemesterId);

        // Fetch required data
        $teachers = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->with('teacher')
            ->get();

        $courses = Courses::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $schoolSemester->specialty_id)
            ->where("semester_id", $schoolSemester->semester_id)
            ->get();

        $teacherSlots = Timetable::where("school_branch_id", $currentSchool->id)
            ->whereIn("teacher_id", $teachers->pluck('teacher_id'))
            ->get()
            ->groupBy('teacher_id');

        $allPossibleSlots = $this->buildAllPossibleSlots($data);
        Log::info("Total possible slots (before availability): {$allPossibleSlots->count()}");

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
            Log::info($messages[count($messages) - 1]);
        }

        $avgAvailabilityPerTeacher = $totalAvailableSlots / $numTeachers;
        if ($data['max_week_slots'] > $avgAvailabilityPerTeacher) {
            $originalMaxWeekSlots = $data['max_week_slots'];
            $data['max_week_slots'] = floor($avgAvailabilityPerTeacher);
            $messages[] = "Adjusted Maximum Weekly Slots from {$originalMaxWeekSlots} to {$data['max_week_slots']} based on average teacher availability ({$avgAvailabilityPerTeacher} slots per teacher).";
            Log::info($messages[count($messages) - 1]);
        }

        // Adjustment 3: Course session limits vs. total slots
        if ($courses->count() * $data['max_week_sessions'] > $totalAvailableSlots) {
            $originalMaxWeekSessions = $data['max_week_sessions'];
            $data['max_week_sessions'] = max(1, floor($totalAvailableSlots / $courses->count()));
            $messages[] = "Adjusted Max weekly sessions from {$originalMaxWeekSessions} to {$data['max_week_sessions']} because the total available slots ({$totalAvailableSlots}) cannot support {$courses->count()} courses each with {$originalMaxWeekSessions} sessions.";
            Log::info($messages[count($messages) - 1]);
        }

        // Adjustment 4: Max courses per day vs. days and teachers
        $maxCoursesPerDayPossible = floor($totalAvailableSlots / ($numDays * $data['max_week_sessions']));
        if ($data['max_courses_day'] > $maxCoursesPerDayPossible) {
            $originalMaxCoursesDay = $data['max_courses_day'];
            $data['max_courses_day'] = $maxCoursesPerDayPossible;
            $messages[] = "Adjusted max courses day from {$originalMaxCoursesDay} to {$data['max_courses_day']} to align with available slots and weekly session limits.";
            Log::info($messages[count($messages) - 1]);
        }

        // Adjustment 5: Consecutive limit and min_gap feasibility
        // For simplicity, cap consecutive_limit based on max_day_slots
        if ($data['consecutive_limit'] > $data['max_day_slots']) {
            $originalConsecutiveLimit = $data['consecutive_limit'];
            $data['consecutive_limit'] = $data['max_day_slots'];
            $messages[] = "Adjusted consecutive limit from {$originalConsecutiveLimit} to {$data['consecutive_limit']} as it cannot exceed max_day_slots.";
            Log::info($messages[count($messages) - 1]);
        }

        // If min_gap is too large, it might reduce effective slots; warn but don't adjust unless critical
        if ($data['min_gap'] > 0) {
            // Rough estimate: min_gap reduces density
            $effectiveSlots = $totalAvailableSlots * (1 - $data['min_gap'] / 10); // Heuristic reduction
            if ($minRequiredSlots > $effectiveSlots) {
                $originalMinGap = $data['min_gap'];
                $data['min_gap'] = 0;
                $messages[] = "Disabled Minimum Course Gap (from {$originalMinGap} to 0) because it would reduce effective slots below the minimum required ({$minRequiredSlots}).";
                Log::info($messages[count($messages) - 1]);
            }
        }

        // Final feasibility check
        if ($minRequiredSlots > $totalAvailableSlots) {
            // Even after adjustments, if still impossible, throw error
            throw new \RuntimeException("Constraints remain unrealistic even after adjustments: Required {$minRequiredSlots} slots, but only {$totalAvailableSlots} available.");
        }

        // Extension: Calculate teacher busyness and sort in ascending order (least busy first)
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

        // Sort by busyness ascending (least busy first)
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

    /**
     * Generate all possible slots without considering availability (for preprocessing).
     *
     * @param array $data Configuration data
     * @return Collection All possible slots
     */
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

    /**
     * Build availability per teacher by excluding busy slots.
     *
     * @param mixed $teachers Collection of teachers
     * @param mixed $teacherSlots Existing timetable slots
     * @param Collection $availableSlots All possible slots
     * @param array $data Configuration data
     * @return array Teacher availability map
     */
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
