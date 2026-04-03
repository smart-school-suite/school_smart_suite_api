<?php

namespace App\Schedular\SemesterTimetable\Grid;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod  as BreakPeriodViolation;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodViolation;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationViolation;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Exceptions\HardConstraintFailureException;

class GridBuilder
{
    public function buildGrid(State $state, array $requestPayload): void
    {
        $constraints = $this->extractConstraints($requestPayload);
        $this->validateConstraints($constraints, $state);

        $grid = [];
        foreach ($constraints['opDays'] as $rawDay) {
            $day = strtolower($rawDay);
            foreach ($this->buildDaySlots($day, $constraints) as $slot) {
                $grid[] = $slot;
            }
        }

        $state->grid = $grid;
    }
    public function extractConstraints(array $requestPayload): array
    {
        $hc = $requestPayload['hard_constraints'] ?? [];

        return [
            'opStartTime'     => $hc['operational_period']['start_time']      ?? '08:00',
            'opEndTime'       => $hc['operational_period']['end_time']         ?? '17:00',
            'opDays'          => $hc["operational_period"]["operational_days"] ?? [],
            'opDayExceptions' => $this->indexByDay(
                $hc['operational_period']['day_exceptions'] ?? []
            ),

            'periodDuration'  => (int) ($hc['schedule_period_duration_minutes']['duration_minutes'] ?? 60),
            'pdExceptions'    => $this->indexByDay(
                $hc['schedule_period_duration_minutes']['day_exceptions'] ?? [],
                valueKey: 'duration_minutes'
            ),

            'bpStartTime'     => $hc['break_period']['start_time']          ?? null,
            'bpEndTime'       => $hc['break_period']['end_time']            ?? null,
            'noBp'            => $hc['break_period']['no_break_exceptions'] ?? false,
            'bpDayExceptions' => $this->indexByDay(
                $hc['break_period']['day_exceptions'] ?? []
            ),

            'jointCourses'    => $this->indexByDay(
                $hc['required_joint_course_periods'] ?? [],
                valueKey: null,
                groupMultiple: true
            ),
        ];
    }
    public function validateConstraints(array $constraints, State $state): void
    {
        $state->violations["hard"] = [
            ...$this->validatePeriodDurations($constraints),
            ...$this->validateBreakPeriods($constraints),
            ...$this->validateJointCourses($constraints),
        ];
        if (!empty($state->violations["hard"])) {
            throw new HardConstraintFailureException();
        }
    }
    public function validatePeriodDurations(array $constraints): array
    {
        $reasons = [];

        foreach ($constraints['opDays'] as $rawDay) {
            $day      = strtolower($rawDay);
            $window   = $this->resolveOperationalWindow($day, $constraints);
            $duration = $this->resolvePeriodDuration($day, $constraints);
            $span     = $window['end'] - $window['start'];

            if ($span < $duration) {
                $reasons[] = [
                    'constraint_failed' => [
                        'key'      => PeriodDurationConstraint::KEY,
                        'day'      => $day,
                        'duration' => $duration,
                    ],
                    'blockers' => [
                        [
                            'key' => OperationalPeriodViolation::KEY,
                            'start_time' => $this->toTime($window['start']),
                            'end_time'   => $this->toTime($window['end']),
                        ]
                    ],
                ];
            }
        }

        return $reasons;
    }
    public function validateBreakPeriods(array $constraints): array
    {
        $reasons = [];

        foreach ($constraints['opDays'] as $rawDay) {
            $day      = strtolower($rawDay);
            $breakWin = $this->resolveBreakWindow($day, $constraints);

            if ($breakWin === null) {
                continue;
            }

            $opWin = $this->resolveOperationalWindow($day, $constraints);

            if (!$this->overlapsBreak($opWin['start'], $opWin['end'], $breakWin)) {
                $reasons[] = [
                    'constraint_failed' => [
                        'key'        => BreakPeriodConstraint::KEY,
                        'day'        => $day,
                        'start_time' => $this->toTime($breakWin['start']),
                        'end_time'   => $this->toTime($breakWin['end']),
                    ],
                    'blockers' => [
                        [
                            'key' => OperationalPeriodViolation::KEY,
                            'start_time' => $this->toTime($opWin['start']),
                            'end_time'   => $this->toTime($opWin['end']),
                            "day"        => $day,
                        ]
                    ],
                ];
            }
        }

        return $reasons;
    }
    public function validateJointCourses(array $constraints): array
    {
        $reasons = [];

        foreach ($constraints['jointCourses'] as $day => $courses) {
            $opWin    = $this->resolveOperationalWindow($day, $constraints);
            $breakWin = $this->resolveBreakWindow($day, $constraints);
            $duration = $this->resolvePeriodDuration($day, $constraints);

            foreach ($courses as $jc) {
                $jcStart    = $this->toMinutes($jc['start_time']);
                $jcEnd      = $this->toMinutes($jc['end_time']);
                $jcDuration = $jcEnd - $jcStart;
                $violations = [];

                if ($jcStart < $opWin['start'] || $jcEnd > $opWin['end']) {
                    $violations[] = [
                        'key'     => OperationalPeriodViolation::KEY,
                        'start_time' => $this->toTime($opWin['start']),
                        'end_time'   => $this->toTime($opWin['end']),
                        "day"        => $day,

                    ];
                }

                if ($breakWin && $this->overlapsBreak($jcStart, $jcEnd, $breakWin)) {
                    $violations[] = [
                        'key'     => BreakPeriodViolation::KEY,
                        'start_time' => $this->toTime($breakWin['start']),
                        'end_time'   => $this->toTime($breakWin['end']),
                        "day"        => $day,

                    ];
                }

                if ($jcDuration !== $duration) {
                    $violations[] = [
                        'key'      => PeriodDurationViolation::KEY,
                        'expected_duration' => $duration,
                        'actual_duration'   => $jcDuration,
                        "day"               => $day,

                    ];
                }

                if (!empty($violations)) {
                    $reasons[] = [
                        'constraint_failed' => [
                            'key'        => RequiredJointCourse::KEY,
                            'day'        => $day,
                            'course_id'  => $jc['course_id']  ?? null,
                            'teacher_id' => $jc['teacher_id'] ?? null,
                            'hall_id'    => $jc['hall_id']    ?? null,
                            'start_time' => $jc['start_time'],
                            'end_time'   => $jc['end_time'],
                        ],
                        'blockers' => $violations,
                    ];
                }
            }
        }
        return $reasons;
    }
    public function buildDaySlots(string $day, array $constraints): array
    {
        $opWindow = $this->resolveOperationalWindow($day, $constraints);
        $duration = $this->resolvePeriodDuration($day, $constraints);
        $breakWin = $this->resolveBreakWindow($day, $constraints);

        $slots = $this->generateRegularSlots($day, $opWindow, $duration, $breakWin);

        if ($breakWin !== null) {
            $slots = $this->insertBreakSlot($slots, $day, $breakWin);
        }

        return $this->overlayJointCourses($slots, $day, $constraints['jointCourses']);
    }
    public function resolveOperationalWindow(string $day, array $constraints): array
    {
        if (isset($constraints['opDayExceptions'][$day])) {
            $ex = $constraints['opDayExceptions'][$day];
            return [
                'start' => $this->toMinutes($ex['start_time']),
                'end'   => $this->toMinutes($ex['end_time']),
            ];
        }

        return [
            'start' => $this->toMinutes($constraints['opStartTime']),
            'end'   => $this->toMinutes($constraints['opEndTime']),
        ];
    }
    public function resolvePeriodDuration(string $day, array $constraints): int
    {
        return isset($constraints['pdExceptions'][$day])
            ? (int) $constraints['pdExceptions'][$day]
            : (int) $constraints['periodDuration'];
    }
    public function resolveBreakWindow(string $day, array $constraints): ?array
    {
        if ($this->dayHasNoBreak($day, $constraints['noBp'])) {
            return null;
        }

        if (isset($constraints['bpDayExceptions'][$day])) {
            $ex = $constraints['bpDayExceptions'][$day];
            return [
                'start' => $this->toMinutes($ex['start_time']),
                'end'   => $this->toMinutes($ex['end_time']),
            ];
        }

        if ($constraints['bpStartTime'] && $constraints['bpEndTime']) {
            return [
                'start' => $this->toMinutes($constraints['bpStartTime']),
                'end'   => $this->toMinutes($constraints['bpEndTime']),
            ];
        }

        return null;
    }
    public function generateRegularSlots(
        string $day,
        array  $opWindow,
        int    $duration,
        ?array $breakWindow
    ): array {
        $slots  = [];
        $cursor = $opWindow['start'];

        while ($cursor + $duration <= $opWindow['end']) {
            $slotStart = $cursor;
            $slotEnd   = $cursor + $duration;

            if ($breakWindow && $this->overlapsBreak($slotStart, $slotEnd, $breakWindow)) {
                // Capture the whole overlapping slot as the break slot and stop
                // advancing into the break — insertBreakSlot will add it with
                // the correct slot boundaries.
                $cursor = $slotEnd; // skip past this slot entirely
                continue;
            }

            $slots[] = $this->makeSlot($day, $slotStart, $slotEnd, GridSlotDTO::TYPE_REGULAR);
            $cursor  = $slotEnd;
        }

        return $slots;
    }
    public function insertBreakSlot(array $slots, string $day, array $breakWindow): array
    {
        // Find the slot whose boundaries contain the break start
        $capturedStart = null;
        $capturedEnd   = null;
        $insertAt      = null;

        foreach ($slots as $i => $slot) {
            $sStart = $this->toMinutes($slot->start_time);
            $sEnd   = $this->toMinutes($slot->end_time);

            if ($sStart <= $breakWindow['start'] && $sEnd >= $breakWindow['start']) {
                // This is the slot the break falls within — capture its boundaries
                $capturedStart = $sStart;
                $capturedEnd   = $sEnd;
                $insertAt      = $i;
                break;
            }
        }

        // If no regular slot was found that contains the break start (e.g. break
        // lands exactly on a slot boundary), fall back to the raw break window
        $breakSlotStart = $capturedStart ?? $breakWindow['start'];
        $breakSlotEnd   = $capturedEnd   ?? $breakWindow['end'];

        $breakSlot = $this->makeSlot($day, $breakSlotStart, $breakSlotEnd, GridSlotDTO::TYPE_BREAK);

        if ($insertAt !== null) {
            // Replace the captured regular slot with the break slot
            array_splice($slots, $insertAt, 1, [$breakSlot]);
        } else {
            // Break falls after all regular slots — append
            $slots[] = $breakSlot;
        }

        return $slots;
    }
    public function overlayJointCourses(array $slots, string $day, array $jointByDay): array
    {
        $dayCourses = $jointByDay[$day] ?? [];

        if (empty($dayCourses)) {
            return $slots;
        }

        $jcIndex = [];
        foreach ($dayCourses as $jc) {
            $key           = $jc['start_time'] . '-' . $jc['end_time'];
            $jcIndex[$key] = $jc;
        }

        foreach ($slots as $slot) {
            if ($slot->isBreak()) {
                continue;
            }

            $key = $slot->start_time . '-' . $slot->end_time;
            if (isset($jcIndex[$key])) {
                $jc = $jcIndex[$key];
                $slot->type       = GridSlotDTO::TYPE_JOINT;
                $slot->course_id  = $jc['course_id']  ?? null;
                $slot->teacher_id = $jc['teacher_id'] ?? null;
                $slot->hall_id    = $jc['hall_id']    ?? null;
            }
        }

        return $slots;
    }

    // ─── Slot factory ─────────────────────────────────────────────────────

    public function makeSlot(string $day, int $startMinutes, int $endMinutes, string $type): GridSlotDTO
    {
        return new GridSlotDTO(
            day: $day,
            start_time: $this->toTime($startMinutes),
            end_time: $this->toTime($endMinutes),
            type: $type,
            teacher_id: null,
            course_id: null,
            hall_id: null,
        );
    }
    public function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return (int) $h * 60 + (int) $m;
    }
    public function toTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
    public function overlapsBreak(int $slotStart, int $slotEnd, array $breakWindow): bool
    {
        return $slotStart < $breakWindow['end'] && $slotEnd > $breakWindow['start'];
    }
    public function dayHasNoBreak(string $day, bool|array $noBp): bool
    {
        if (is_bool($noBp)) {
            return $noBp;
        }

        return in_array($day, array_map('strtolower', $noBp), strict: true);
    }
    public function indexByDay(array $items, ?string $valueKey = null, bool $groupMultiple = false): array
    {
        $index = [];

        foreach ($items as $item) {
            $day   = strtolower($item['day']);
            $value = $valueKey !== null ? $item[$valueKey] : $item;

            if ($groupMultiple) {
                $index[$day][] = $value;
            } else {
                $index[$day] = $value;
            }
        }

        return $index;
    }
}
