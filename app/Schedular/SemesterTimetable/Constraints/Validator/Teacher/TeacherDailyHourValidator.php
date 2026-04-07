<?php

namespace App\Schedular\SemesterTimetable\Constraints\Validator\Teacher;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours as Violation;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use Carbon\Carbon;

class TeacherDailyHourValidator implements ValidatorInterface
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

        // ── 1. Resolve teacher context from slot type ─────────────────────
        $teacherId = match ($slotType) {
            self::CREQUESTEDSLOT      => $context->teachersForCourse($params['course_id'])->first(),
            self::REQUESTEDASSIGNMENT,
            self::TREQUESTEDSLOT      => $params['teacher_id'],
            default                   => $params['teacher_id'] ?? null,
        };

        if (empty($teacherId)) {
            return [];
        }

        $dailyHours = $context->tDailyHoursFor($teacherId);

        if (empty($dailyHours)) {
            return [];
        }

        $maxHours = $dailyHours['max_hours'] ?? null;
        $minHours = $dailyHours['min_hours'] ?? null;

        if ($maxHours === null && $minHours === null) {
            return [];
        }

        // ── 2. Seed windows with the incoming slot ────────────────────────
        $windows = [
            ['start_time' => $startTime, 'end_time' => $endTime],
        ];

        // ── 3. Peel remaining slots from each source, excluding the
        //        incoming slot to avoid double-counting ────────────────────
        $isNotIncoming = fn($slot) =>
            $slot['start_time'] !== $startTime ||
            $slot['end_time']   !== $endTime   ||
            strtolower($slot['day']) !== $day;

        // Teacher requested windows for this teacher on this day
        $context->tRequestedWindowsFor($day)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $windows[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // Course requested slots for all courses this teacher teaches
        $context->coursesForTeacher($teacherId)
            ->each(function ($courseId) use ($context, $day, $isNotIncoming, &$windows) {
                $context->cRequestedWindowsFor($day)
                    ->filter($isNotIncoming)
                    ->each(fn($slot) => $windows[] = [
                        'start_time' => $slot['start_time'],
                        'end_time'   => $slot['end_time'],
                    ]);
            });

        // Requested assignments pinned to this teacher on this day
        $context->requestedAssignmentsFor($day)
            ->filter(fn($slot) => $slot['teacher_id'] === $teacherId)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $windows[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // Teacher busy slots on this day
        $context->tBusySlotsFor($teacherId, $day)
            ->filter($isNotIncoming)
            ->each(fn($slot) => $windows[] = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ]);

        // ── 4. Sum all windows ────────────────────────────────────────────
        $totalMinutes = collect($windows)->sum(fn($w) =>
            Carbon::createFromFormat('H:i', $w['start_time'])
                ->diffInMinutes(Carbon::createFromFormat('H:i', $w['end_time']))
        );

        $totalHours = $totalMinutes / 60;

        // ── 5. Check bounds and return blocker if breached ────────────────
        if ($maxHours !== null && $totalHours >= $maxHours) {
            return [
                'key'             => Violation::KEY,
                'breach'          => 'max',
                'teacher_id'      => $teacherId,
                'day'             => $day,
                'slot_type'       => $slotType,
                'start_time'      => $startTime,
                'end_time'        => $endTime,
                'total_hours'     => round($totalHours, 4),
                'max_hours'       => $maxHours,
                'min_hours'       => $minHours,
            ];
        }

        if ($minHours !== null && $totalHours < $minHours) {
            return [
                'key'             => Violation::KEY,
                'breach'          => 'min',
                'teacher_id'      => $teacherId,
                'day'             => $day,
                'slot_type'       => $slotType,
                'start_time'      => $startTime,
                'end_time'        => $endTime,
                'total_hours'     => round($totalHours, 4),
                'max_hours'       => $maxHours,
                'min_hours'       => $minHours,
            ];
        }

        return [];
    }
}
