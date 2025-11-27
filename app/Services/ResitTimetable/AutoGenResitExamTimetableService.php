<?php

namespace App\Services\ResitTimetable;
use App\Models\ResitExam;
use App\Models\Studentresit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
class AutoGenResitExamTimetableService
{
        public function autoGenExamTimetable($currentSchool, $data)
    {
        try {
            // --- Initial Data Retrieval and Validation ---
            $exam = ResitExam::where("school_branch_id", $currentSchool->id)
                    ->with(['exam'])
                    ->find($data['exam_id']);

            if (!$exam) {
                Log::error('Exam not found', ['exam_id' => $data['exam_id'], 'school_id' => $currentSchool->id]);
                return ['error' => 'Exam not found'];
            }
            $resitableCourses = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $exam->specialty_id)
            ->where("level_id", $exam->level_id)
            ->with(['courses', 'exam' => function ($query) use ($exam) {
                $query->where("semester_id", $exam->semester_id);
            }])
            ->get();

            $courses = $resitableCourses->pluck('courses')->unique()->values();

            if ($courses->isEmpty()) {
                Log::warning('No courses found for semester', ['semester_id' => $exam->semester_id]);
                return ['error' => 'No courses found for this semester'];
            }

            $startTime = $data['start_time'];
            $endTime = $data['end_time'];
            $startDate = $exam->start_date;
            $endDate = $exam->end_date;
            $minCoursePerDay = max(1, (int)$data['min_course_per_day']); // Ensure minimum is at least 1
            $maxCoursePerDay = max($minCoursePerDay, (int)$data['max_course_per_day']); // Ensure max >= min
            $courseDuration = max(30, (int)$data['course_duration']); // Minimum 30 minutes

            $baseDate = Carbon::today();
            $startCarbonTime = Carbon::createFromFormat('Y-m-d H:i', $baseDate->format('Y-m-d') . ' ' . $startTime);
            $endCarbonTime = Carbon::createFromFormat('Y-m-d H:i', $baseDate->format('Y-m-d') . ' ' . $endTime);

            // If the above fails, try alternative parsing
            if (!$startCarbonTime) {
                $startCarbonTime = $baseDate->copy()->setTimeFromTimeString($startTime);
            }
            if (!$endCarbonTime) {
                $endCarbonTime = $baseDate->copy()->setTimeFromTimeString($endTime);
            }

            // Ensure end time is after start time (handle same day)
            if ($endCarbonTime->lte($startCarbonTime)) {
                $endCarbonTime->addDay();
            }

            $totalAvailableMinutes = $startCarbonTime->diffInMinutes($endCarbonTime);

            if ($totalAvailableMinutes < $courseDuration) {
                Log::error('Not enough time in day for course duration', [
                    'available_minutes' => $totalAvailableMinutes,
                    'course_duration' => $courseDuration,
                    'start_time_parsed' => $startCarbonTime->format('H:i'),
                    'end_time_parsed' => $endCarbonTime->format('H:i')
                ]);
                return ['error' => "Course duration ({$courseDuration} min) exceeds available daily time ({$totalAvailableMinutes} min)"];
            }

            // --- Algorithm Start ---
            $generatedTimetable = [];
            $remainingCourses = $courses->shuffle()->all();
            $allDates = [];

            // Step 1: Generate list of exam dates
            $currentDate = Carbon::parse($startDate);
            $endCarbonDate = Carbon::parse($endDate);

            if ($currentDate->gt($endCarbonDate)) {
                Log::error('Start date is after end date', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
                return ['error' => 'Invalid date range: start date is after end date'];
            }

            while ($currentDate->lte($endCarbonDate)) {
                $allDates[] = $currentDate->toDateString();
                $currentDate->addDay();
            }

            if (empty($allDates)) {
                Log::error('No valid exam dates generated');
                return ['error' => 'No valid exam dates available'];
            }

            shuffle($allDates);
            Log::info('Generated exam dates', ['dates_count' => count($allDates)]);

            // Step 2: Schedule courses across dates
            $dateIndex = 0;
            $maxAttempts = count($allDates) * 2; // Prevent infinite loops
            $attempts = 0;

            while (!empty($remainingCourses) && $dateIndex < count($allDates) && $attempts < $maxAttempts) {
                $attempts++;
                $currentExamDate = $allDates[$dateIndex];

                // Determine courses to schedule today
                $coursesToScheduleToday = min(
                    rand($minCoursePerDay, $maxCoursePerDay),
                    count($remainingCourses)
                );

                Log::debug('Scheduling for date', [
                    'date' => $currentExamDate,
                    'courses_to_schedule' => $coursesToScheduleToday,
                    'remaining_courses' => count($remainingCourses)
                ]);

                $scheduledToday = 0;
                $usedTimeSlots = []; // Track used time slots to avoid conflicts

                // Step 3: Schedule courses for current day
                for ($i = 0; $i < $coursesToScheduleToday && !empty($remainingCourses); $i++) {
                    $courseToSchedule = array_shift($remainingCourses);

                    // Generate random start time with conflict checking
                    $examTimes = $this->generateRandomExamTime(
                        $startTime,
                        $endTime,
                        $courseDuration,
                        $usedTimeSlots
                    );

                    if ($examTimes === null) {
                        // No available time slot, put course back and try next date
                        array_unshift($remainingCourses, $courseToSchedule);
                        break;
                    }

                    // Add to used time slots
                    $usedTimeSlots[] = [
                        'start' => $examTimes['start_carbon'],
                        'end' => $examTimes['end_carbon']
                    ];

                    // Add to timetable
                    $generatedTimetable[] = [
                        'course_id' => $courseToSchedule->id,
                        'course_name' => $courseToSchedule->course_title,
                        'course_code' => $courseToSchedule->course_code,
                        'course_credit' => $courseToSchedule->credit,
                        'exam_date' => $currentExamDate,
                        'duration' => $this->formatDuration($data['course_duration'], true),
                        'start_time' => $examTimes['start_time'],
                        'end_time' => $examTimes['end_time']
                    ];

                    $scheduledToday++;
                }

                Log::debug('Scheduled courses for date', [
                    'date' => $currentExamDate,
                    'scheduled_count' => $scheduledToday
                ]);

                $dateIndex++;
            }

            // Check if all courses were scheduled
            if (!empty($remainingCourses)) {
                Log::warning('Not all courses could be scheduled', [
                    'remaining_courses' => count($remainingCourses),
                    'scheduled_courses' => count($generatedTimetable)
                ]);
            }

            Log::info('Timetable generation completed', [
                'total_scheduled' => count($generatedTimetable),
                'remaining_unscheduled' => count($remainingCourses)
            ]);

            // Step 4: Group by date and format
            $groupedTimetable = $this->groupTimetableByDate($generatedTimetable);
            return $groupedTimetable;

        } catch (\Exception $e) {
            Log::error('Error generating exam timetable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['error' => 'An error occurred while generating the timetable: ' . $e->getMessage()];
        }
    }

    /**
     * Generate a random exam time that doesn't conflict with existing time slots
     */
    private function generateRandomExamTime($startTime, $endTime, $courseDuration, $usedTimeSlots, $maxAttempts = 50)
    {
        // Parse times ensuring they're on the same day
        $baseDate = Carbon::today();
        $startCarbonTime = Carbon::createFromFormat('Y-m-d H:i', $baseDate->format('Y-m-d') . ' ' . $startTime);
        $endCarbonTime = Carbon::createFromFormat('Y-m-d H:i', $baseDate->format('Y-m-d') . ' ' . $endTime);

        // Fallback to alternative parsing if format parsing fails
        if (!$startCarbonTime) {
            $startCarbonTime = $baseDate->copy()->setTimeFromTimeString($startTime);
        }
        if (!$endCarbonTime) {
            $endCarbonTime = $baseDate->copy()->setTimeFromTimeString($endTime);
        }

        // Ensure end time is after start time
        if ($endCarbonTime->lte($startCarbonTime)) {
            $endCarbonTime->addDay();
        }

        $totalAvailableMinutes = $startCarbonTime->diffInMinutes($endCarbonTime);
        if ($totalAvailableMinutes < $courseDuration) {
            Log::warning('Cannot fit course in time window', [
                'available_minutes' => $totalAvailableMinutes,
                'course_duration' => $courseDuration,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            return null;
        }

        // Calculate possible start times at 30-minute intervals
        $interval = 30; // 30-minute intervals
        $possibleStartMinutes = [];
        $currentMinute = 0;

        while ($currentMinute <= $totalAvailableMinutes - $courseDuration) {
            $possibleStartMinutes[] = $currentMinute;
            $currentMinute += $interval;
        }

        if (empty($possibleStartMinutes)) {
            Log::warning('No valid start times available for course duration', [
                'total_available_minutes' => $totalAvailableMinutes,
                'course_duration' => $courseDuration
            ]);
            return null;
        }

        // Shuffle possible start times to randomize selection
        shuffle($possibleStartMinutes);

        for ($attempt = 0; $attempt < min($maxAttempts, count($possibleStartMinutes)); $attempt++) {
            $randomStartMinute = $possibleStartMinutes[$attempt];
            $examStartDateTime = $startCarbonTime->copy()->addMinutes($randomStartMinute);
            $examEndDateTime = $examStartDateTime->copy()->addMinutes($courseDuration);

            // Check for conflicts with existing time slots
            $hasConflict = false;
            foreach ($usedTimeSlots as $usedSlot) {
                if ($this->timeSlotsOverlap($examStartDateTime, $examEndDateTime, $usedSlot['start'], $usedSlot['end'])) {
                    $hasConflict = true;
                    break;
                }
            }

            if (!$hasConflict) {
                return [
                    'start_time' => $examStartDateTime->format('g:i A'),
                    'end_time' => $examEndDateTime->format('g:i A'),
                    'start_carbon' => $examStartDateTime,
                    'end_carbon' => $examEndDateTime
                ];
            }
        }

        Log::warning('Could not find non-conflicting time slot after maximum attempts', [
            'max_attempts' => $maxAttempts,
            'used_slots_count' => count($usedTimeSlots)
        ]);

        return null; // Could not find a non-conflicting time slot
    }

    /**
     * Check if two time slots overlap
     */
    private function timeSlotsOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lt($end2) && $end1->gt($start2);
    }

    /**
     * Group timetable entries by formatted date
     */
    private function groupTimetableByDate($generatedTimetable)
    {
        $groupedTimetable = [];

        foreach ($generatedTimetable as $examEntry) {
            $formattedDate = Carbon::parse($examEntry['exam_date'])->format('D d M Y');

            $groupedTimetable[$formattedDate][] = $examEntry;
        }

        // Sort each day's exams by start time
        foreach ($groupedTimetable as $date => &$dayExams) {
            usort($dayExams, function($a, $b) {
                $timeA = Carbon::parse($a['start_time']);
                $timeB = Carbon::parse($b['start_time']);
                return $timeA->timestamp <=> $timeB->timestamp;
            });
        }

        return $groupedTimetable;
    }

    private function formatDuration($minutes, $detailed = true)
    {
        if ($minutes < 60) {
            return "$minutes min";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return "$hours h";
        }

        if ($detailed) {
            return "$hours h $remainingMinutes min";
        } else {
            return "$hours h";
        }
    }
}
