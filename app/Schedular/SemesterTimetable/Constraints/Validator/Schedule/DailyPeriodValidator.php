<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Schedule;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;

class DailyPeriodValidator implements ValidatorInterface
{
    protected const TREQUESTEDSLOT      = TeacherRequestedTimeSlot::KEY;
    protected const REQUESTEDASSIGNMENT = RequestedAssignment::KEY;
    protected const CREQUESTEDSLOT      = CourseRequestedSlot::KEY;

    public function check(ConstraintContext $context, array $params): array
    {
        $day       = strtolower($params['day']);
        $startTime = $params['start_time'];
        $endTime   = $params['end_time'];
        $slotType  = $params['slot_type'];

        $dailyPeriodConstraint = $context->dailyPeriodsFor($day);

        if (empty($dailyPeriodConstraint)) {
            return [];
        }

        $maxPeriods = $dailyPeriodConstraint['max_periods'] ?? null;
        $minPeriods = $dailyPeriodConstraint['min_periods'] ?? null;

        if ($maxPeriods === null && $minPeriods === null) {
            return [];
        }

        // ── Resolve teacher context from slot type ────────────────────────
        $teacherId = match ($slotType) {
            self::CREQUESTEDSLOT      => $context->teachersForCourse($params['course_id'])->first(),
            self::REQUESTEDASSIGNMENT,
            self::TREQUESTEDSLOT      => $params['teacher_id'],
            default                   => $params['teacher_id'] ?? null,
        };

        if (empty($teacherId)) {
            return [];
        }

        // ── Seed with the incoming slot ───────────────────────────────────
        $periods = [
            ['start_time' => $startTime, 'end_time' => $endTime],
        ];

        // ── Exclude incoming slot from all sources ────────────────────────
        $isNotIncoming = fn($slot) =>
            $slot['start_time']      !== $startTime ||
            $slot['end_time']        !== $endTime   ||
            strtolower($slot['day']) !== $day;

        // Teacher requested windows for this teacher on this day
        $context->tRequestedWindowsFor($day)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $periods[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // Course requested slots for all courses this teacher teaches
        $context->coursesForTeacher($teacherId)
            ->each(function ($courseId) use ($context, $day, $isNotIncoming, &$periods) {
                $context->cRequestedWindowsFor($day)
                    ->filter($isNotIncoming)
                    ->each(fn($slot) => $periods[] = [
                        'start_time' => $slot['start_time'],
                        'end_time'   => $slot['end_time'],
                    ]);
            });

        // Requested assignments pinned to this teacher on this day
        $context->requestedAssignmentsFor($day)
            ->filter(fn($slot) => $slot['teacher_id'] === $teacherId)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $periods[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // Joint courses on this day — always included as they are fixed periods
        $context->jointCourses($day)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $periods[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // ── Count ─────────────────────────────────────────────────────────
        $totalPeriods = count($periods);

        // ── Check bounds ──────────────────────────────────────────────────
        if ($maxPeriods !== null && $totalPeriods >= $maxPeriods) {
            return [
                'key'            => ScheduleDailyPeriod::KEY,
                'breach'         => 'over',
                'day'            => $day,
                'slot_type'      => $slotType,
                'teacher_id'     => $teacherId,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'total_periods'  => $totalPeriods,
                'max_periods'    => $maxPeriods,
                'min_periods'    => $minPeriods,
            ];
        }

        if ($minPeriods !== null && $totalPeriods < $minPeriods) {
            return [
                'key'            => ScheduleDailyPeriod::KEY,
                'breach'         => 'under',
                'day'            => $day,
                'slot_type'      => $slotType,
                'teacher_id'     => $teacherId,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'total_periods'  => $totalPeriods,
                'max_periods'    => $maxPeriods,
                'min_periods'    => $minPeriods,
            ];
        }

        return [];
    }
}
