<?php

namespace App\Services;

use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AutomaticTimetableService
{
    /**
     * Generate a random timetable based on the provided configuration.
     *
     * @param mixed $currentSchool The current school object
     * @param int $schoolSemesterId The ID of the school semester
     * @param array $data Configuration data including days, slot lengths, and constraints
     * @return array The generated timetable assignments
     */
    public function generateRandomTimetable($currentSchool, $schoolSemesterId, array $data)
    {
        // Validate configuration
        if (!isset($data['min_slot_length'], $data['max_slot_length'], $data['slot_increment'])) {
            throw new \InvalidArgumentException("Configuration must include min_slot_length, max_slot_length, and slot_increment.");
        }
        if ($data['min_slot_length'] > $data['max_slot_length'] || $data['slot_increment'] <= 0) {
            throw new \InvalidArgumentException("Invalid slot length configuration: min_slot_length must be <= max_slot_length, and slot_increment must be positive.");
        }

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

        // Log input data for debugging
        Log::info("Generating timetable: Teachers: {$teachers->count()}, Courses: {$courses->count()}");

        // 1. Build all available slots
        $availableSlots = $this->buildAvailableSlots($data);
        Log::info("Total available slots: {$availableSlots->count()}");

        // Validate feasibility
        $requiredSlots = $teachers->count() * $data['min_day_slots'] * count($data['days']);
        if ($requiredSlots > $availableSlots->count() && !$data['allow_doubles']) {
            Log::warning("Potential slot shortage: Need {$requiredSlots} slots for {$teachers->count()} teachers, but only {$availableSlots->count()} available.");
        }

        // 2. Build teacher availability map
        $teacherAvailability = $this->mapTeacherAvailability($teachers, $teacherSlots, $availableSlots, $data);

        // 3. Constrained assignment
        $results = $this->assignCoursesConstrained($courses, $teacherAvailability, $availableSlots, $data);

        // Log minimum goal failures
        $this->logMinimumFailures($results['teacherWeekUsage'], $data);
        $this->logDailyMinimumFailures($results['teacherDayUsage'], $data);

        return $results['assigned'];
    }

    /**
     * Generate slots with variable durations based on min_slot_length, max_slot_length, and slot_increment.
     *
     * @param array $data Configuration data
     * @return Collection Available slots
     */
    protected function buildAvailableSlots(array $data): Collection
    {
        $slots = collect();
        $minSlotLength = (int) $data['min_slot_length'];
        $maxSlotLength = (int) $data['max_slot_length'];
        $slotIncrement = (int) $data['slot_increment'];

        // Generate possible slot lengths (e.g., 90, 120, 150)
        $slotLengths = [];
        for ($length = $minSlotLength; $length <= $maxSlotLength; $length += $slotIncrement) {
            $slotLengths[] = $length;
        }

        foreach ($data['days'] as $day) {
            $start = strtotime($data['start']);
            $end = strtotime($data['end']);
            $current = $start;

            // Generate slots with varying durations
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
                $current += $slotIncrement * 60;
            }
        }

        return $slots->sortBy(['day', 'timestamp'])->values();
    }

    /**
     * Build availability per teacher by excluding their busy slots.
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

        foreach ($teachers as $teacher) {
            $busy = $teacherSlots->get($teacher->teacher_id, collect());

            $available = $availableSlots->sortBy(function ($slot) use ($data) {
                $dayOrder = array_flip($data['days'])[$slot['day']] ?? 99;
                return $dayOrder * 10000 + $slot['timestamp'];
            })->values();

            $map[$teacher->teacher_id] = $available->reject(function ($slot) use ($busy) {
                return $busy->contains(function ($b) use ($slot) {
                    return $b->day === $slot['day'] &&
                           $b->start_time === $slot['start'] &&
                           $b->end_time === $slot['end'];
                });
            })->values();
        }

        return $map;
    }

    /**
     * Assign courses to teachers and slots, respecting constraints.
     *
     * @param mixed $courses Collection of courses
     * @param array $teacherAvailability Teacher availability map
     * @param Collection $availableSlots All possible slots
     * @param array $data Configuration data
     * @return array Assignment results
     */
    protected function assignCoursesConstrained($courses, array $teacherAvailability, Collection $availableSlots, array $data): array
    {
        $assigned = [];
        $teacherDayUsage = [];
        $teacherWeekUsage = [];
        $courseDayUsage = [];
        $lastAssignedSlot = [];

        // Initialize tracking
        foreach (array_keys($teacherAvailability) as $teacherId) {
            $teacherWeekUsage[$teacherId] = 0;
            $teacherDayUsage[$teacherId] = array_fill_keys($data['days'], 0);
            $lastAssignedSlot[$teacherId] = array_fill_keys($data['days'], null);
        }
        foreach ($courses as $course) {
            $courseDayUsage[$course->id] = array_fill_keys($data['days'], 0);
        }

        // Pre-assignment phase: Ensure min_day_slots for each teacher
        foreach (array_keys($teacherAvailability) as $teacherId) {
            foreach ($data['days'] as $day) {
                $slotsAssigned = 0;
                $availableTeacherSlots = $teacherAvailability[$teacherId]->filter(fn($s) => $s['day'] === $day);

                while ($slotsAssigned < $data['min_day_slots'] && $availableTeacherSlots->isNotEmpty()) {
                    $filteredSlots = $availableTeacherSlots->filter(function ($slot) use ($teacherId, $teacherDayUsage, $courseDayUsage, $lastAssignedSlot, $data, $courses) {
                        $day = $slot['day'];
                        if ($teacherDayUsage[$teacherId][$day] >= $data['max_day_slots']) {
                            return false;
                        }
                        $availableCourses = $courses->filter(fn($c) => $courseDayUsage[$c->id][$day] < $data['max_courses_day']);
                        if ($availableCourses->isEmpty()) {
                            return false;
                        }
                        $lastSlot = $lastAssignedSlot[$teacherId][$day];
                        if ($lastSlot) {
                            $isConsecutive = $this->isSlotConsecutive($lastSlot, $slot);
                            if ($isConsecutive && ($teacherDayUsage[$teacherId][$day] + 1) > $data['consecutive_limit']) {
                                return false;
                            }
                            if (!$isConsecutive && $data['min_gap'] > 0) {
                                $requiredGapTime = $data['min_gap'] * $slot['duration'] * 60;
                                if (($slot['timestamp'] - $lastSlot['timestamp'] - ($lastSlot['duration'] * 60)) < $requiredGapTime) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    });

                    if ($filteredSlots->isEmpty()) {
                        Log::warning("Cannot assign min_day_slots for teacher {$teacherId} on {$day}: No valid slots.");
                        break;
                    }

                    $slot = $filteredSlots->random();
                    $availableCourses = $courses->filter(fn($c) => $courseDayUsage[$c->id][$day] < $data['max_courses_day']);
                    if ($availableCourses->isEmpty()) {
                        Log::warning("No available courses for teacher {$teacherId} on {$day}.");
                        break;
                    }
                    $course = $availableCourses->random();

                    $assigned[] = [
                        'course_title' => $course->course_title,
                        'course_code' => $course->course_code,
                        'course_id' => $course->id,
                        'teacher_id' => $teacherId,
                        'teacher_name' => Teacher::find($teacherId)->name,
                        'day' => $slot['day'],
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'duration' => $slot['duration']
                    ];

                    $teacherDayUsage[$teacherId][$slot['day']]++;
                    $teacherWeekUsage[$teacherId]++;
                    $courseDayUsage[$course->id][$slot['day']]++;
                    $lastAssignedSlot[$teacherId][$slot['day']] = $slot;

                    $teacherAvailability[$teacherId] = $teacherAvailability[$teacherId]->reject(fn($s) => $s['key'] === $slot['key'])->values();
                    if (!$data['allow_doubles']) {
                        foreach (array_keys($teacherAvailability) as $tId) {
                            $teacherAvailability[$tId] = $teacherAvailability[$tId]->reject(function ($s) use ($slot) {
                                $sStart = $s['timestamp'];
                                $sEnd = $s['timestamp'] + ($s['duration'] * 60);
                                $slotStart = $slot['timestamp'];
                                $slotEnd = $slot['timestamp'] + ($slot['duration'] * 60);
                                return $s['day'] === $slot['day'] && $sStart < $slotEnd && $sEnd > $slotStart;
                            })->values();
                        }
                    }

                    $slotsAssigned++;
                    $availableTeacherSlots = $teacherAvailability[$teacherId]->filter(fn($s) => $s['day'] === $day);
                }

                if ($slotsAssigned < $data['min_day_slots']) {
                    Log::warning("Failed to assign min_day_slots ({$data['min_day_slots']}) for teacher {$teacherId} on {$day}. Assigned: {$slotsAssigned}.");
                }
            }
        }

        // Main assignment phase: Assign remaining courses
        foreach ($courses->shuffle() as $course) {
            $availableTeachers = collect(array_keys($teacherAvailability))
                ->filter(fn($tId) => $teacherWeekUsage[$tId] < $data['max_week_slots']);

            if ($availableTeachers->isEmpty()) {
                Log::warning("No teacher has remaining weekly slots for course: {$course->id}");
                continue;
            }

            $teacherId = $availableTeachers->random();
            $slots = $teacherAvailability[$teacherId];

            if ($slots->isEmpty()) {
                Log::info("Skipping course {$course->id}: No available time slots for teacher {$teacherId}.");
                continue;
            }

            $filteredSlots = $slots->filter(function ($slot) use ($teacherId, $course, $teacherDayUsage, $courseDayUsage, $lastAssignedSlot, $data) {
                $day = $slot['day'];
                if ($teacherDayUsage[$teacherId][$day] >= $data['max_day_slots']) {
                    return false;
                }
                if ($courseDayUsage[$course->id][$day] >= $data['max_courses_day']) {
                    return false;
                }
                $lastSlot = $lastAssignedSlot[$teacherId][$day];
                if ($lastSlot) {
                    $isConsecutive = $this->isSlotConsecutive($lastSlot, $slot);
                    if ($isConsecutive && ($teacherDayUsage[$teacherId][$day] + 1) > $data['consecutive_limit']) {
                        return false;
                    }
                    if (!$isConsecutive && $data['min_gap'] > 0) {
                        $requiredGapTime = $data['min_gap'] * $slot['duration'] * 60;
                        if (($slot['timestamp'] - $lastSlot['timestamp'] - ($lastSlot['duration'] * 60)) < $requiredGapTime) {
                            return false;
                        }
                    }
                }
                return true;
            });

            if ($filteredSlots->isEmpty()) {
                Log::info("Skipping course {$course->id}: Slots filtered out by constraints for teacher {$teacherId}.");
                continue;
            }

            $slot = $filteredSlots->random();
            $assigned[] = [
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
                'course_id' => $course->id,
                'teacher_id' => $teacherId,
                'teacher_name' => Teacher::find($teacherId)->name,
                'day' => $slot['day'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'duration' => $slot['duration']
            ];

            $teacherDayUsage[$teacherId][$slot['day']]++;
            $teacherWeekUsage[$teacherId]++;
            $courseDayUsage[$course->id][$slot['day']]++;
            $lastAssignedSlot[$teacherId][$slot['day']] = $slot;

            $teacherAvailability[$teacherId] = $slots->reject(fn($s) => $s['key'] === $slot['key'])->values();
            if (!$data['allow_doubles']) {
                foreach (array_keys($teacherAvailability) as $tId) {
                    $teacherAvailability[$tId] = $teacherAvailability[$tId]->reject(function ($s) use ($slot) {
                        $sStart = $s['timestamp'];
                        $sEnd = $s['timestamp'] + ($s['duration'] * 60);
                        $slotStart = $slot['timestamp'];
                        $slotEnd = $slot['timestamp'] + ($slot['duration'] * 60);
                        return $s['day'] === $slot['day'] && $sStart < $slotEnd && $sEnd > $slotStart;
                    })->values();
                }
            }
        }

        return [
            'assigned' => $assigned,
            'teacherWeekUsage' => $teacherWeekUsage,
            'teacherDayUsage' => $teacherDayUsage
        ];
    }

    /**
     * Check if a new slot follows immediately after the last assigned slot.
     *
     * @param array $lastSlot The previously assigned slot
     * @param array $newSlot The slot to check
     * @return bool Whether the slots are consecutive
     */
    protected function isSlotConsecutive(array $lastSlot, array $newSlot): bool
    {
        if ($lastSlot['day'] !== $newSlot['day']) {
            return false;
        }

        $expectedNewStartTimestamp = $lastSlot['timestamp'] + ($lastSlot['duration'] * 60);
        return $expectedNewStartTimestamp === $newSlot['timestamp'];
    }

    /**
     * Logs teachers who did not meet the minimum weekly slot goal.
     *
     * @param array $teacherWeekUsage Weekly slot counts per teacher
     * @param array $data Configuration data
     */
    protected function logMinimumFailures(array $teacherWeekUsage, array $data): void
    {
        if (!isset($data['min_week_slots'])) return;

        $minWeekSlots = $data['min_week_slots'];

        foreach ($teacherWeekUsage as $teacherId => $count) {
            if ($count < $minWeekSlots) {
                $teacherName = Teacher::find($teacherId)->name ?? 'Unknown Teacher';
                Log::warning("TIMETABLE MIN GOAL FAILURE: Teacher {$teacherName} (ID: {$teacherId}) only assigned {$count} slots, falling short of the minimum weekly goal of {$minWeekSlots}.");
            }
        }
    }

    /**
     * Logs teachers who did not meet the minimum daily slot goal.
     *
     * @param array $teacherDayUsage Daily slot counts per teacher
     * @param array $data Configuration data
     */
    protected function logDailyMinimumFailures(array $teacherDayUsage, array $data): void
    {
        if (!isset($data['min_day_slots'])) return;

        foreach ($teacherDayUsage as $teacherId => $days) {
            foreach ($days as $day => $count) {
                if ($count < $data['min_day_slots']) {
                    $teacherName = Teacher::find($teacherId)->name ?? 'Unknown Teacher';
                    Log::warning("DAILY MIN GOAL FAILURE: Teacher {$teacherName} (ID: {$teacherId}) only assigned {$count} slots on {$day}, falling short of the minimum daily goal of {$data['min_day_slots']}.");
                }
            }
        }
    }
}
