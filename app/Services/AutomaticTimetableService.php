<?php

namespace App\Services;

use App\Models\Courses;
use App\Models\SchoolSemester;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\TeacherSpecailtyPreference;
use App\Services\AutoGenTimetablePreprocessorService;

class AutomaticTimetableService
{
    protected AutoGenTimetablePreprocessorService $autoGenTimetablePreprocessorService;

    public function __construct(AutoGenTimetablePreprocessorService $autoGenTimetablePreprocessorService)
    {
        $this->autoGenTimetablePreprocessorService = $autoGenTimetablePreprocessorService;
    }

    /**
     * Generate a random timetable based on the provided configuration, grouped by day.
     *
     * @param mixed $currentSchool The current school object
     * @param string $schoolSemesterId The ID of the school semester
     * @param array $data Configuration data including days, slot lengths, and constraints
     * @return array The generated timetable assignments grouped by day and preprocessor messages
     */
    public function generateRandomTimetable($currentSchool, $schoolSemesterId, array $data)
    {
        // Preprocess the configuration
        $preprocessResult = $this->autoGenTimetablePreprocessorService->preprocess($currentSchool, $schoolSemesterId, $data);
        $adjustedData = $preprocessResult['adjusted_data'];
        $messages = $preprocessResult['messages'];
        $teacherPriorityOrder = $preprocessResult['teacher_priority_order'] ?? [];

        $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolSemesterId);

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

        $availableSlots = $this->buildAvailableSlots($adjustedData);

        $requiredSlots = $teachers->count() * $adjustedData['min_day_slots'] * count($adjustedData['days']);
        if ($requiredSlots > $availableSlots->count() && !$adjustedData['allow_doubles']) {
            Log::warning("Potential slot shortage: Need {$requiredSlots} slots for {$teachers->count()} teachers, but only {$availableSlots->count()} available.");
            $messages[] = "Warning: Potential slot shortage detected. Required {$requiredSlots} slots, but only {$availableSlots->count()} available.";
        }

        $teacherAvailability = $this->mapTeacherAvailability($teachers, $teacherSlots, $availableSlots, $adjustedData);

        $results = $this->assignCoursesConstrained($courses, $teacherAvailability, $availableSlots, $adjustedData, $teacherPriorityOrder);

        $this->logMinimumFailures($results['teacherWeekUsage'], $adjustedData);
        $this->logDailyMinimumFailures($results['teacherDayUsage'], $adjustedData);

        $this->validateTimetableForClashes($results['assigned']);

        $groupedByDay = [];
        foreach ($results['assigned'] as $assignment) {
            $day = $assignment['day'];
            if (!isset($groupedByDay[$day])) {
                $groupedByDay[$day] = [];
            }
            $groupedByDay[$day][] = $assignment;
        }

        foreach ($groupedByDay as $day => &$assignments) {
            usort($assignments, function ($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
        }

        return [
            'timetable' => $groupedByDay,
            'messages' => $messages
        ];
    }

    /**
     * Format duration in minutes to a readable string (e.g., 90 to "1 h 30 min").
     *
     * @param int $minutes Duration in minutes
     * @return string Formatted duration
     */
    protected function formatDuration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours} h {$remainingMinutes} min";
        } elseif ($hours > 0) {
            return "{$hours} h";
        } else {
            return "{$remainingMinutes} min";
        }
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
     * Build availability per teacher by excluding all busy slots across teachers.
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

    /**
     * Assign courses to teachers and slots, respecting constraints and prioritizing less busy teachers.
     *
     * @param mixed $courses Collection of courses
     * @param array $teacherAvailability Teacher availability map
     * @param Collection $availableSlots All possible slots
     * @param array $data Configuration data
     * @param array $teacherPriorityOrder Ordered teacher IDs (least busy first)
     * @return array Assignment results
     */
    protected function assignCoursesConstrained($courses, array $teacherAvailability, Collection $availableSlots, array $data, array $teacherPriorityOrder): array
    {
        $assigned = [];
        $teacherDayUsage = [];
        $teacherWeekUsage = [];
        $courseDayUsage = [];
        $courseWeekUsage = [];
        $lastAssignedSlot = [];
        $occupiedSlots = [];

        // Initialize tracking
        foreach (array_keys($teacherAvailability) as $teacherId) {
            $teacherWeekUsage[$teacherId] = 0;
            $teacherDayUsage[$teacherId] = array_fill_keys($data['days'], 0);
            $lastAssignedSlot[$teacherId] = array_fill_keys($data['days'], null);
            $occupiedSlots[$teacherId] = collect();
        }
        foreach ($courses as $course) {
            $courseDayUsage[$course->id] = array_fill_keys($data['days'], 0);
            $courseWeekUsage[$course->id] = 0;
        }

        // Pre-assignment phase: Ensure min_day_slots for each teacher, prioritizing less busy teachers
        foreach ($teacherPriorityOrder as $teacherId) {
            if (!isset($teacherAvailability[$teacherId])) {
                continue;
            }
            foreach ($data['days'] as $day) {
                $slotsAssigned = 0;
                $availableTeacherSlots = $teacherAvailability[$teacherId]->filter(fn($s) => $s['day'] === $day);

                while ($slotsAssigned < $data['min_day_slots'] && $availableTeacherSlots->isNotEmpty()) {
                    $filteredSlots = $availableTeacherSlots->filter(function ($slot) use ($teacherId, $teacherDayUsage, $courseDayUsage, $courseWeekUsage, $lastAssignedSlot, $data, $courses, $occupiedSlots) {
                        $day = $slot['day'];
                        if ($teacherDayUsage[$teacherId][$day] >= $data['max_day_slots']) {
                            return false;
                        }
                        $availableCourses = $courses->filter(fn($c) =>
                            $courseDayUsage[$c->id][$day] < $data['max_courses_day'] &&
                            $courseWeekUsage[$c->id] < $data['max_week_sessions']
                        );
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
                        return !$occupiedSlots[$teacherId]->contains(function ($occupied) use ($slot) {
                            $o_start = $occupied['timestamp'];
                            $o_end = $o_start + ($occupied['duration'] * 60);
                            $slot_start = $slot['timestamp'];
                            $slot_end = $slot_start + ($slot['duration'] * 60);
                            return $occupied['day'] === $slot['day'] &&
                                   $slot_start < $o_end &&
                                   $slot_end > $o_start;
                        });
                    });

                    if ($filteredSlots->isEmpty()) {
                        Log::warning("Cannot assign min_day_slots for teacher {$teacherId} on {$day}: No valid slots.");
                        break;
                    }

                    $slot = $filteredSlots->random();
                    $availableCourses = $courses->filter(fn($c) =>
                        $courseDayUsage[$c->id][$day] < $data['max_courses_day'] &&
                        $courseWeekUsage[$c->id] < $data['max_week_sessions']
                    );
                    if ($availableCourses->isEmpty()) {
                        Log::warning("No available courses for teacher {$teacherId} on {$day}.");
                        break;
                    }
                    $course = $availableCourses->random();

                    $assignment = [
                        'course_title' => $course->course_title,
                        'course_code' => $course->course_code,
                        'course_credit' => $course->credit,
                        'course_id' => $course->id,
                        'teacher_id' => $teacherId,
                        'teacher_name' => Teacher::find($teacherId)->name,
                        'day' => $slot['day'],
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'duration' => $this->formatDuration($slot['duration']),
                        'timestamp' => $slot['timestamp'],
                        'duration_minutes' => $slot['duration']
                    ];

                    $assigned[] = $assignment;
                    $occupiedSlots[$teacherId]->push($slot);
                    $teacherDayUsage[$teacherId][$slot['day']]++;
                    $teacherWeekUsage[$teacherId]++;
                    $courseDayUsage[$course->id][$slot['day']]++;
                    $courseWeekUsage[$course->id]++;
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
            }
        }

        // Main assignment phase: Assign remaining sessions, prioritizing less busy teachers
        $maxAttempts = $courses->count() * 10;
        $failedAttempts = 0;

        while (true) {
            $availableCourses = $courses->filter(fn($c) => $courseWeekUsage[$c->id] < $data['max_week_sessions']);
            if ($availableCourses->isEmpty()) {
                break;
            }

            $course = $availableCourses->random();
            $availableTeachers = collect($teacherPriorityOrder)
                ->filter(fn($tId) => isset($teacherAvailability[$tId]) && $teacherWeekUsage[$tId] < $data['max_week_slots'] && $teacherAvailability[$tId]->isNotEmpty());

            if ($availableTeachers->isEmpty()) {
                $failedAttempts++;
                if ($failedAttempts > $maxAttempts) {
                    Log::warning("Max failed attempts reached. Stopping main assignment.");
                    break;
                }
                continue;
            }

            $teacherId = $availableTeachers->first();
            $slots = $teacherAvailability[$teacherId];

            $filteredSlots = $slots->filter(function ($slot) use ($teacherId, $course, $teacherDayUsage, $courseDayUsage, $courseWeekUsage, $lastAssignedSlot, $data, $occupiedSlots) {
                $day = $slot['day'];
                if ($teacherDayUsage[$teacherId][$day] >= $data['max_day_slots']) {
                    return false;
                }
                if ($courseDayUsage[$course->id][$day] >= $data['max_courses_day']) {
                    return false;
                }
                if ($courseWeekUsage[$course->id] >= $data['max_week_sessions']) {
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
                return !$occupiedSlots[$teacherId]->contains(function ($occupied) use ($slot) {
                    $o_start = $occupied['timestamp'];
                    $o_end = $o_start + ($occupied['duration'] * 60);
                    $slot_start = $slot['timestamp'];
                    $slot_end = $slot_start + ($slot['duration'] * 60);
                    return $occupied['day'] === $slot['day'] &&
                           $slot_start < $o_end &&
                           $slot_end > $o_start;
                });
            });

            if ($filteredSlots->isEmpty()) {
                $failedAttempts++;
                if ($failedAttempts > $maxAttempts) {
                    Log::warning("Max failed attempts reached. Stopping main assignment.");
                    break;
                }
                continue;
            }

            $slot = $filteredSlots->random();
            $assignment = [
                'course_title' => $course->course_title,
                'course_code' => $course->course_code,
                'course_credit' => $course->credit,
                'course_id' => $course->id,
                'teacher_id' => $teacherId,
                'teacher_name' => Teacher::find($teacherId)->name,
                'day' => $slot['day'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'duration' => $this->formatDuration($slot['duration']),
                'timestamp' => $slot['timestamp'],
                'duration_minutes' => $slot['duration']
            ];

            $assigned[] = $assignment;
            $occupiedSlots[$teacherId]->push($slot);
            $teacherDayUsage[$teacherId][$slot['day']]++;
            $teacherWeekUsage[$teacherId]++;
            $courseDayUsage[$course->id][$slot['day']]++;
            $courseWeekUsage[$course->id]++;
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

            $failedAttempts = 0;
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
                Log::warning("Teacher {$teacherName} (ID: {$teacherId}) assigned {$count} slots, below minimum weekly goal of {$minWeekSlots}.");
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
                    Log::warning("Teacher {$teacherName} (ID: {$teacherId}) assigned {$count} slots on {$day}, below minimum daily goal of {$data['min_day_slots']}.");
                }
            }
        }
    }

    /**
     * Validate the generated timetable for clashes and log any issues.
     *
     * @param array $assigned The assigned timetable slots
     */
    protected function validateTimetableForClashes(array $assigned): void
    {
        $clashes = [];
        $assignmentsByTeacher = collect($assigned)->groupBy('teacher_id');

        foreach ($assignmentsByTeacher as $teacherId => $teacherAssignments) {
            $teacherAssignments = $teacherAssignments->sortBy('timestamp')->values();
            for ($i = 0; $i < $teacherAssignments->count(); $i++) {
                for ($j = $i + 1; $j < $teacherAssignments->count(); $j++) {
                    $slot1 = $teacherAssignments[$i];
                    $slot2 = $teacherAssignments[$j];

                    if ($slot1['day'] !== $slot2['day']) {
                        continue;
                    }

                    $start1 = $slot1['timestamp'];
                    $end1 = $start1 + ($slot1['duration_minutes'] * 60);
                    $start2 = $slot2['timestamp'];
                    $end2 = $start2 + ($slot2['duration_minutes'] * 60);

                    if ($start1 < $end2 && $end1 > $start2) {
                        $clashes[] = [
                            'teacher_id' => $teacherId,
                            'teacher_name' => $slot1['teacher_name'],
                            'slot1' => [
                                'course' => $slot1['course_title'],
                                'day' => $slot1['day'],
                                'start' => $slot1['start_time'],
                                'end' => $slot1['end_time']
                            ],
                            'slot2' => [
                                'course' => $slot2['course_title'],
                                'day' => $slot2['day'],
                                'start' => $slot2['start_time'],
                                'end' => $slot2['end_time']
                            ]
                        ];
                    }
                }
            }
        }

        if (!empty($clashes)) {
            foreach ($clashes as $clash) {
                Log::error("Clash detected for teacher {$clash['teacher_name']} (ID: {$clash['teacher_id']}): " .
                           "Course '{$clash['slot1']['course']}' on {$clash['slot1']['day']} from {$clash['slot1']['start']} to {$clash['slot1']['end']}, " .
                           "conflicts with Course '{$clash['slot2']['course']}' on {$clash['slot2']['day']} from {$clash['slot2']['start']} to {$clash['slot2']['end']}.");
            }
            throw new \RuntimeException("Timetable generation failed: Clashes detected. Check logs for details.");
        }
    }
}
