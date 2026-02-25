<?php

namespace Database\Seeders;

use App\Exceptions\AppException;
use App\Models\Course\JointCourseSlot;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
use App\Models\SpecialtyHall;
use App\Models\Teacher;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class GenerateTimetableTestCase extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolSemesters = SchoolSemester::where("school_branch_id", "50207b5e-65fb-46ca-b507-963931071777")
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->take(3)
            ->get();
        foreach ($schoolSemesters as $schoolSemester) {
            $teachers = $this->getTeachers(
                $schoolSemester->school_branch_id,
                $schoolSemester->specialty_id
            );
            $teacherCourses = $this->getTeacherCourses(
                $schoolSemester->school_branch_id,
                $teachers->pluck('id')->toArray(),
                $schoolSemester
            );
            $halls = $this->getHalls(
                $schoolSemester->school_branch_id,
                $schoolSemester->specialty_id
            );
            $hallBusyPeriods = $this->getHallBusyPeriods(
                $schoolSemester->school_branch_id,
                $halls
            );
            $teacherBusyPeriods = $this->getTeacherBusyPeriods(
                $schoolSemester->school_branch_id,
                $teachers->pluck('id')->toArray()
            );

            $teacherPreferredSlots = $this->getTeacherPreferredSlots(
                $schoolSemester->school_branch_id,
                $teachers->pluck('id')->toArray()
            );

            $jointCourses = JointCourseSlot::where("school_branch_id", $schoolSemester->school_branch_id)
                ->whereHas('semesterJointCourse.semesterJointCourseRef', function ($query) use ($schoolSemester) {
                    $query->where("school_semester_id", $schoolSemester->id);
                })
                ->with(['semesterJointCourse.semesterJointCourseRef'])
                ->get();

            $operationalPeriod = $this->generateOperationalPeriod();
            $breakPeriod = $this->generateBreakPeriod($operationalPeriod['operational_days']);
            $data = [

                "teachers" => $teachers->map(fn($teacher) => [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                ]),
                "teacher_courses" => $teacherCourses->map(fn($teacherCourse) => [
                    'teacher_id' => $teacherCourse->teacher_id,
                    'course_id' => $teacherCourse->course_id,
                    'course_name' => $teacherCourse->course->course_title,
                    'course_type' => $teacherCourse->course->types->pluck('name')->toArray(),
                ]),
                "teacher_busy_periods" => $teacherBusyPeriods->map(fn($period) => [
                    'teacher_id' => $period->teacher_id,
                    'day' => $period->day,
                    'start_time' => Carbon::parse($period->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($period->end_time)->format('H:i'),
                ]),
                "halls" => $halls->map(fn($hall) => [
                    'hall_id' => $hall->hall_id,
                    'hall_name' => $hall->hall->name,
                    "hall_capacity" => $hall->hall->capacity,
                    'hall_type' => $hall->hall->types->pluck('name')->toArray(),
                ]),
                "hall_busy_periods" => $hallBusyPeriods->map(fn($period) => [
                    'hall_id' => $period->hall_id,
                    'day' => $period->day,
                    'start_time' => Carbon::parse($period->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($period->end_time)->format('H:i'),
                ]),
                "hard_constraints" => [
                    "operational_period" => $operationalPeriod,
                    "break_period" => $breakPeriod,
                    "schedule_period_duration_minutes" => $this->generateSchedulePeriodDurationMinutes($operationalPeriod['operational_days']),
                    "required_joint_course_periods" => $jointCourses->isNotEmpty()
                        ? $jointCourses
                        ->groupBy(['course_id', 'teacher_id'])
                        ->flatMap(fn($teachers) => $teachers->map(fn($group, $teacherId) => [
                            'course_id'  => $group->first()->course_id,
                            'teacher_id' => $teacherId,
                            'periods'    => $group->map(fn($j) => [
                                'day'        => $j->day,
                                'start_time' => Carbon::parse($j->start_time)->format('H:i'),
                                'end_time'   => Carbon::parse($j->end_time)->format('H:i'),
                                'hall_id'    => $j->hall_id,
                            ])->values()
                        ]))->values()
                        : []
                ],
                "soft_constraints" => [
                    "teacher_max_daily_hours" => $this->generateTeacherMaxDailyHours($teachers->toArray()),
                    "teacher_max_weekly_hours" => $this->generateTeacherMaxWeeklyHours($teachers->toArray()),
                    "schedule_max_periods_per_day" => $this->generateScheduleMaxPeriodsPerDay($operationalPeriod['operational_days']),
                    "schedule_max_free_periods_per_day" => $this->generateScheduleMaxFreePeriodsPerDay($operationalPeriod['operational_days']),
                    "course_max_daily_frequency" => $this->generateCourseMaxDailyFrequency($teacherCourses->toArray()),
                    "course_requested_time_slots" => $this->generateCourseRequestedTimeSlots($teacherCourses->toArray(), $operationalPeriod['operational_days']),
                    "requested_assignments" => $this->generateRequestedAssignments($teacherCourses->toArray(), $halls->pluck('hall')->toArray(), $operationalPeriod['operational_days']),
                    "hall_requested_time_windows" => $this->generateHallRequestedTimeWindows($halls->pluck('hall')->toArray(), $operationalPeriod['operational_days']),
                    "teacher_requested_time_windows" => $this->generateTeacherRequestedTimeWindows($teachers->toArray(), $operationalPeriod['operational_days']),
                    "requested_free_periods" => $this->generateRequestedFreePeriods($operationalPeriod['operational_days'])
                ]
            ];

            $json = json_encode($data, JSON_PRETTY_PRINT);
            $fileName = $schoolSemester->id . '_test_case.json';
            $directory = public_path('without-preference-test');

            File::ensureDirectoryExists($directory);

            $filePath = $directory . '/' . $fileName;

            File::put($filePath, $json);

            $this->command->info('JSON file created at: ' . $filePath);
        }
    }

    //hard constraint generators
    private function generateBreakPeriod($operationalDays)
    {
        $breakStartHours = ['11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00'];
        $breakDurations = [30, 45, 60];

        $defaultStartTime = $breakStartHours[array_rand($breakStartHours)];
        $defaultDuration = $breakDurations[array_rand($breakDurations)];
        $defaultEndTime = $this->addMinutesToTime($defaultStartTime, $defaultDuration);

        $dayExceptions = [];
        $numberOfExceptions = rand(0, min(4, count($operationalDays)));

        if ($numberOfExceptions > 0) {
            $shuffledDays = $operationalDays;
            shuffle($shuffledDays);
            $exceptionDays = array_slice($shuffledDays, 0, $numberOfExceptions);

            foreach ($exceptionDays as $day) {
                $exceptionStartTime = $breakStartHours[array_rand($breakStartHours)];
                $exceptionDuration = $breakDurations[array_rand($breakDurations)];
                $exceptionEndTime = $this->addMinutesToTime($exceptionStartTime, $exceptionDuration);

                $dayExceptions[] = [
                    'day' => strtolower($day),
                    'start_time' => $exceptionStartTime,
                    'end_time' => $exceptionEndTime
                ];
            }
        }

        $noBreakExceptions = [];
        $numberOfNoBreakDays = rand(0, min(2, count($operationalDays)));

        if ($numberOfNoBreakDays > 0) {
            $remainingDays = array_diff($operationalDays, array_column($dayExceptions, 'day'));
            $shuffledRemainingDays = array_values($remainingDays);
            shuffle($shuffledRemainingDays);
            $noBreakDays = array_slice($shuffledRemainingDays, 0, $numberOfNoBreakDays);

            $noBreakExceptions = array_map('strtolower', $noBreakDays);
        }

        return [
            'start_time' => $defaultStartTime,
            'end_time' => $defaultEndTime,
            'day_exceptions' => $dayExceptions,
            'no_break_exceptions' => $noBreakExceptions
        ];
    }
    private function addMinutesToTime($time, $minutes)
    {
        $timestamp = strtotime($time);
        $newTimestamp = strtotime("+{$minutes} minutes", $timestamp);
        return date('H:i', $newTimestamp);
    }
    private function generateOperationalPeriod()
    {
        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $operationalDaysCount = rand(5, 7);

        $shuffledDays = $allDays;
        shuffle($shuffledDays);
        $operationalDays = array_slice($shuffledDays, 0, $operationalDaysCount);
        sort($operationalDays);

        $startHours = ['06:00', '07:00', '07:30', '08:00', '08:30', '09:00'];
        $endHours = ['16:00', '17:00', '17:30', '18:00', '18:30', '19:00', '20:00'];

        $defaultStartTime = $startHours[array_rand($startHours)];
        $defaultEndTime = $endHours[array_rand($endHours)];

        $dayExceptions = [];
        $numberOfExceptions = rand(0, min(3, count($operationalDays)));

        if ($numberOfExceptions > 0) {
            $shuffledOperationalDays = $operationalDays;
            shuffle($shuffledOperationalDays);
            $exceptionDays = array_slice($shuffledOperationalDays, 0, $numberOfExceptions);

            foreach ($exceptionDays as $day) {
                $isWeekend = in_array($day, ['saturday', 'sunday']);

                if ($isWeekend) {
                    $weekendStarts = array_slice($startHours, 2);
                    $exceptionStartTime = $weekendStarts[array_rand($weekendStarts)];

                    $weekendEnds = ['12:00', '13:00', '14:00', '15:00'];
                    $exceptionEndTime = $weekendEnds[array_rand($weekendEnds)];
                } else {
                    $exceptionStartTime = $startHours[array_rand($startHours)];
                    $exceptionEndTime = $endHours[array_rand($endHours)];
                }

                $dayExceptions[] = [
                    'day' => strtolower($day),
                    'start_time' => $exceptionStartTime,
                    'end_time' => $exceptionEndTime
                ];
            }
        }

        return [
            'start_time' => $defaultStartTime,
            'end_time' => $defaultEndTime,
            'day_exceptions' => $dayExceptions,
            'operational_days' => $operationalDays
        ];
    }
    private function generateSchedulePeriodDurationMinutes($operationalDays)
    {
        $commonDurations = [15, 30, 45, 60, 90, 120];

        $defaultDuration = $commonDurations[array_rand($commonDurations)];

        $dayExceptions = [];
        $numberOfExceptions = rand(0, min(3, count($operationalDays)));

        if ($numberOfExceptions > 0) {
            $shuffledDays = $operationalDays;
            shuffle($shuffledDays);
            $exceptionDays = array_slice($shuffledDays, 0, $numberOfExceptions);

            foreach ($exceptionDays as $day) {
                $availableDurations = array_diff($commonDurations, [$defaultDuration]);
                $exceptionDuration = $availableDurations[array_rand($availableDurations)];

                $dayExceptions[] = [
                    'day' => strtolower($day),
                    'duration_minutes' => $exceptionDuration
                ];
            }
        }

        return [
            'duration_minutes' => $defaultDuration,
            'day_exceptions' => $dayExceptions
        ];
    }
    //soft constraint generators
    private function generateTeacherMaxDailyHours($teachers)
    {
        $commonMaxHours = [4, 5, 6, 7, 8, 9, 10];

        $defaultMaxHours = $commonMaxHours[array_rand($commonMaxHours)];

        $teacherExceptions = [];
        $numberOfExceptions = rand(0, min(5, count($teachers)));

        if ($numberOfExceptions > 0) {
            $shuffledTeachers = $teachers;
            shuffle($shuffledTeachers);
            $exceptionTeachers = array_slice($shuffledTeachers, 0, $numberOfExceptions);

            foreach ($exceptionTeachers as $teacher) {
                $availableHours = array_diff($commonMaxHours, [$defaultMaxHours]);
                $exceptionMaxHours = $availableHours[array_rand($availableHours)];

                $teacherId = is_array($teacher) ? ($teacher['id'] ?? $teacher['teacher_id']) : $teacher;

                $teacherExceptions[] = [
                    'teacher_id' => $teacherId,
                    'max_hours' => $exceptionMaxHours
                ];
            }
        }

        return [
            'max_hours' => $defaultMaxHours,
            'teacher_exceptions' => $teacherExceptions
        ];
    }
    private function generateTeacherMaxWeeklyHours($teachers)
    {
        $commonMaxHours = [20, 25, 30, 35, 38, 40, 45, 48];

        $defaultMaxHours = $commonMaxHours[array_rand($commonMaxHours)];

        $teacherExceptions = [];
        $numberOfExceptions = rand(0, min(6, count($teachers)));

        if ($numberOfExceptions > 0) {
            $shuffledTeachers = $teachers;
            shuffle($shuffledTeachers);
            $exceptionTeachers = array_slice($shuffledTeachers, 0, $numberOfExceptions);

            foreach ($exceptionTeachers as $teacher) {
                $availableHours = array_diff($commonMaxHours, [$defaultMaxHours]);
                $exceptionMaxHours = $availableHours[array_rand($availableHours)];

                $teacherId = is_array($teacher) ? ($teacher['id'] ?? $teacher['teacher_id']) : $teacher;

                $teacherExceptions[] = [
                    'teacher_id' => $teacherId,
                    'max_hours' => $exceptionMaxHours
                ];
            }
        }

        return [
            'max_hours' => $defaultMaxHours,
            'teacher_exceptions' => $teacherExceptions
        ];
    }
    private function generateScheduleMaxPeriodsPerDay($operationalDays)
    {
        $commonMaxPeriods = [4, 5, 6, 7, 8, 9, 10];

        $defaultMaxPeriods = $commonMaxPeriods[array_rand($commonMaxPeriods)];

        $dayExceptions = [];
        $numberOfExceptions = rand(0, min(4, count($operationalDays)));

        if ($numberOfExceptions > 0) {
            $shuffledDays = $operationalDays;
            shuffle($shuffledDays);
            $exceptionDays = array_slice($shuffledDays, 0, $numberOfExceptions);

            foreach ($exceptionDays as $day) {
                $availablePeriods = array_diff($commonMaxPeriods, [$defaultMaxPeriods]);
                $exceptionMaxPeriods = $availablePeriods[array_rand($availablePeriods)];

                $dayExceptions[] = [
                    'day' => strtolower($day),
                    'max_periods' => $exceptionMaxPeriods
                ];
            }
        }

        return [
            'max_periods' => $defaultMaxPeriods,
            'day_exceptions' => $dayExceptions
        ];
    }
    private function generateScheduleMaxFreePeriodsPerDay($operationalDays)
    {
        $commonMaxFreePeriods = [0, 1, 2, 3, 4];

        $defaultMaxFreePeriods = $commonMaxFreePeriods[array_rand($commonMaxFreePeriods)];

        $dayExceptions = [];
        $numberOfExceptions = rand(0, min(4, count($operationalDays)));

        if ($numberOfExceptions > 0) {
            $shuffledDays = $operationalDays;
            shuffle($shuffledDays);
            $exceptionDays = array_slice($shuffledDays, 0, $numberOfExceptions);

            foreach ($exceptionDays as $day) {
                $availableFreePeriods = array_diff($commonMaxFreePeriods, [$defaultMaxFreePeriods]);
                $exceptionMaxFreePeriods = $availableFreePeriods[array_rand($availableFreePeriods)];

                $dayExceptions[] = [
                    'day' => strtolower($day),
                    'max_free_periods' => $exceptionMaxFreePeriods
                ];
            }
        }

        return [
            'max_free_periods' => $defaultMaxFreePeriods,
            'day_exceptions' => $dayExceptions
        ];
    }
    private function generateCourseMaxDailyFrequency($courses)
    {
        $commonMaxFrequency = [1, 2, 3, 4];

        $defaultMaxFrequency = $commonMaxFrequency[array_rand($commonMaxFrequency)];

        $courseExceptions = [];
        $numberOfExceptions = rand(0, min(5, count($courses)));

        if ($numberOfExceptions > 0) {
            $shuffledCourses = $courses;
            shuffle($shuffledCourses);
            $exceptionCourses = array_slice($shuffledCourses, 0, $numberOfExceptions);

            foreach ($exceptionCourses as $course) {
                $availableFrequency = array_diff($commonMaxFrequency, [$defaultMaxFrequency]);
                $exceptionMaxFrequency = $availableFrequency[array_rand($availableFrequency)];

                $courseId = is_array($course) ? ($course['id'] ?? $course['course_id']) : $course;

                $courseExceptions[] = [
                    'course_id' => $courseId,
                    'max_frequency' => $exceptionMaxFrequency
                ];
            }
        }

        return [
            'max_frequency' => $defaultMaxFrequency,
            'course_exceptions' => $courseExceptions
        ];
    }
    private function generateCourseRequestedTimeSlots($courses, $operationalDays)
    {
        $timeSlots = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00'
        ];

        $coursesWithTimeSlots = [];
        $numberOfCoursesWithSlots = rand(0, min(4, count($courses)));

        if ($numberOfCoursesWithSlots > 0) {
            $shuffledCourses = $courses;
            shuffle($shuffledCourses);
            $selectedCourses = array_slice($shuffledCourses, 0, $numberOfCoursesWithSlots);

            foreach ($selectedCourses as $course) {
                $courseId = is_array($course) ? ($course['id'] ?? $course['course_id']) : $course;
                $requestedTimeSlots = [];
                $numberOfSlots = rand(1, min(4, count($operationalDays)));
                $slotTypes = ['day_only', 'time_only', 'day_and_time'];

                for ($i = 0; $i < $numberOfSlots; $i++) {
                    $slotType = $slotTypes[array_rand($slotTypes)];
                    $slot = [];

                    if ($slotType === 'day_only') {
                        $slot['day'] = $operationalDays[array_rand($operationalDays)];
                    } else {
                        if ($slotType === 'day_and_time') {
                            $slot['day'] = $operationalDays[array_rand($operationalDays)];
                        }

                        $startOptions = array_slice($timeSlots, 0, -1);
                        $startTime = $startOptions[array_rand($startOptions)];
                        $startIndex = array_search($startTime, $timeSlots);

                        $maxIndex = count($timeSlots) - 1;
                        $endIndex = rand($startIndex + 1, min($startIndex + 3, $maxIndex));

                        $slot['start_time'] = $startTime;
                        $slot['end_time'] = $timeSlots[$endIndex];
                    }

                    $requestedTimeSlots[] = $slot;
                }

                $coursesWithTimeSlots[] = [
                    'course_id' => $courseId,
                    'requested_time_slots' => $requestedTimeSlots
                ];
            }
        }

        return $coursesWithTimeSlots;
    }
    private function generateRequestedAssignments($teacherCourses, $halls, $operationalDays)
    {
        $timeSlots = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00'
        ];

        $requestedAssignments = [];
        $numberOfAssignments = rand(1, min(8, count($teacherCourses)));

        if ($numberOfAssignments > 0) {
            $shuffledTeacherCourses = $teacherCourses;
            shuffle($shuffledTeacherCourses);
            $selectedTeacherCourses = array_slice($shuffledTeacherCourses, 0, $numberOfAssignments);

            foreach ($selectedTeacherCourses as $teacherCourse) {
                $courseId = is_array($teacherCourse) ? ($teacherCourse['course_id'] ?? null) : null;
                $teacherId = is_array($teacherCourse) ? ($teacherCourse['teacher_id'] ?? null) : null;

                if (!$courseId || !$teacherId) {
                    continue;
                }

                $hall = $halls[array_rand($halls)];
                $hallId = is_array($hall) ? ($hall['id'] ?? $hall['hall_id']) : $hall;

                $assignmentTypes = ['day_only', 'time_only', 'day_and_time', 'full'];
                $assignmentType = $assignmentTypes[array_rand($assignmentTypes)];

                $assignment = [
                    'course_id' => $courseId,
                    'teacher_id' => $teacherId,
                    'hall_id' => $hallId
                ];

                if ($assignmentType === 'day_only') {
                    $assignment['day'] = $operationalDays[array_rand($operationalDays)];
                } else {
                    // Handle cases that require time: 'time_only', 'day_and_time', and 'full'
                    if ($assignmentType !== 'time_only') {
                        $assignment['day'] = $operationalDays[array_rand($operationalDays)];
                    }

                    $startOptions = array_slice($timeSlots, 0, -1);
                    $startTime = $startOptions[array_rand($startOptions)];
                    $startIndex = array_search($startTime, $timeSlots);

                    $maxIndex = count($timeSlots) - 1;
                    $endIndex = rand($startIndex + 1, min($startIndex + 3, $maxIndex));

                    $assignment['start_time'] = $startTime;
                    $assignment['end_time'] = $timeSlots[$endIndex];
                }

                $requestedAssignments[] = $assignment;
            }
        }

        return $requestedAssignments;
    }
    private function generateHallRequestedTimeWindows($halls, $operationalDays)
    {
        $timeSlots = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00'
        ];

        $hallsWithTimeWindows = [];
        $numberOfHallsWithWindows = rand(0, min(6, count($halls)));

        if ($numberOfHallsWithWindows > 0) {
            $shuffledHalls = $halls;
            shuffle($shuffledHalls);
            $selectedHalls = array_slice($shuffledHalls, 0, $numberOfHallsWithWindows);

            foreach ($selectedHalls as $hall) {
                $hallId = is_array($hall) ? ($hall['id'] ?? $hall['hall_id']) : $hall;

                $requestedTimeWindows = [];
                $numberOfWindows = rand(1, min(4, count($operationalDays)));
                $windowTypes = ['day_only', 'time_only', 'day_and_time'];

                for ($i = 0; $i < $numberOfWindows; $i++) {
                    $windowType = $windowTypes[array_rand($windowTypes)];
                    $window = [];

                    if ($windowType === 'day_only') {
                        $window['day'] = $operationalDays[array_rand($operationalDays)];
                    } else {
                        if ($windowType === 'day_and_time') {
                            $window['day'] = $operationalDays[array_rand($operationalDays)];
                        }

                        $startOptions = array_slice($timeSlots, 0, -1);
                        $startTime = $startOptions[array_rand($startOptions)];
                        $startIndex = array_search($startTime, $timeSlots);

                        $maxIndex = count($timeSlots) - 1;
                        $endIndex = rand($startIndex + 1, min($startIndex + 4, $maxIndex));

                        $window['start_time'] = $startTime;
                        $window['end_time'] = $timeSlots[$endIndex];
                    }

                    $requestedTimeWindows[] = $window;
                }

                $hallsWithTimeWindows[] = [
                    'hall_id' => $hallId,
                    'requested_time_windows' => $requestedTimeWindows
                ];
            }
        }

        return $hallsWithTimeWindows;
    }
    private function generateTeacherRequestedTimeWindows($teachers, $operationalDays)
    {
        $timeSlots = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00'
        ];

        $teachersWithTimeWindows = [];
        $numberOfTeachersWithWindows = rand(0, min(6, count($teachers)));

        if ($numberOfTeachersWithWindows > 0) {
            $shuffledTeachers = $teachers;
            shuffle($shuffledTeachers);
            $selectedTeachers = array_slice($shuffledTeachers, 0, $numberOfTeachersWithWindows);

            foreach ($selectedTeachers as $teacher) {
                $teacherId = is_array($teacher) ? ($teacher['id'] ?? $teacher['teacher_id']) : $teacher;

                $requestedTimeWindows = [];
                $numberOfWindows = rand(1, min(4, count($operationalDays)));
                $windowTypes = ['day_only', 'time_only', 'day_and_time'];

                for ($i = 0; $i < $numberOfWindows; $i++) {
                    $windowType = $windowTypes[array_rand($windowTypes)];
                    $window = [];

                    if ($windowType === 'day_only') {
                        $window['day'] = $operationalDays[array_rand($operationalDays)];
                    } else {
                        if ($windowType === 'day_and_time') {
                            $window['day'] = $operationalDays[array_rand($operationalDays)];
                        }

                        $startOptions = array_slice($timeSlots, 0, -1);
                        $startTime = $startOptions[array_rand($startOptions)];
                        $startIndex = array_search($startTime, $timeSlots);

                        $maxIndex = count($timeSlots) - 1;
                        $endIndex = rand($startIndex + 1, min($startIndex + 4, $maxIndex));

                        $window['start_time'] = $startTime;
                        $window['end_time'] = $timeSlots[$endIndex];
                    }

                    $requestedTimeWindows[] = $window;
                }

                $teachersWithTimeWindows[] = [
                    'teacher_id' => $teacherId,
                    'requested_time_windows' => $requestedTimeWindows
                ];
            }
        }

        return $teachersWithTimeWindows;
    }
    private function generateRequestedFreePeriods($operationalDays)
    {
        $timeSlots = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00'
        ];

        $requestedFreePeriods = [];
        $numberOfFreePeriods = rand(0, min(5, count($operationalDays)));

        if ($numberOfFreePeriods > 0) {
            $shuffledDays = $operationalDays;
            shuffle($shuffledDays);
            $selectedDays = array_slice($shuffledDays, 0, $numberOfFreePeriods);

            foreach ($selectedDays as $day) {
                $periodTypes = ['day_only', 'day_and_time'];
                $periodType = $periodTypes[array_rand($periodTypes)];

                $freePeriod = [
                    'day' => strtolower($day)
                ];

                if ($periodType === 'day_and_time') {
                    $startOptions = array_slice($timeSlots, 0, -1);
                    $startTime = $startOptions[array_rand($startOptions)];
                    $startIndex = array_search($startTime, $timeSlots);

                    $maxIndex = count($timeSlots) - 1;
                    $endIndex = rand($startIndex + 1, min($startIndex + 3, $maxIndex));

                    $freePeriod['start_time'] = $startTime;
                    $freePeriod['end_time'] = $timeSlots[$endIndex];
                }

                $requestedFreePeriods[] = $freePeriod;
            }
        }

        return $requestedFreePeriods;
    }
    //data retrieval functions
    private function getTeachers(string $branchId, string $specialtyId)
    {
        $teachers = Teacher::whereHas('specialtyPreference', function ($q) use ($branchId, $specialtyId) {
            $q->where('school_branch_id', $branchId)
                ->where('specialty_id', $specialtyId);
        })->get();

        if ($teachers->isEmpty()) {
            throw new AppException("No Teachers Found", 404, "No Teachers Found", "...");
        }

        return $teachers;
    }
    private function getTeacherCourses(string $branchId, array $teacherIds, $semester)
    {
        return TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereHas('course', fn($q) =>
            $q->where('semester_id', $semester->semester_id))
            ->with(['course.types', 'teacher'])
            ->get();
    }
    private function getHalls(string $branchId, string $specialtyId)
    {
        $halls = SpecialtyHall::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('hall.types')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned to this specialty",
                404,
                "No Halls Found For this specialty",
                "No Halls Found for specialty {$specialtyId} — please ensure that halls have been assigned to this specialty before creating timetable"
            );
        }

        return $halls;
    }
    private function getHallBusyPeriods(string $branchId, $halls)
    {
        $hallIds = $halls->pluck('hall_id')->toArray();
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereHas('schoolSemester', function ($query) {
                $query->where("end_date", ">=", now());
            })
            ->whereIn('hall_id', $hallIds)
            ->with('hall')
            ->get();
    }
    private function getTeacherBusyPeriods(string $branchId, array $teacherIds)
    {
        return SemesterTimetableSlot::where('school_branch_id', $branchId)
            ->whereHas('schoolSemester', function ($query) {
                $query->where("end_date", ">=", now());
            })
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }
    private function getTeacherPreferredSlots(string $branchId, array $teacherIds)
    {
        return InstructorAvailabilitySlot::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }
}
