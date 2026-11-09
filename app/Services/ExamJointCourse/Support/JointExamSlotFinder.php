<?php

namespace App\Services\ExamJointCourse\Support;

use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Finds the times a joint exam can take place: a time every linked
 * specialty's students are free, with enough free exam hall seats for all
 * of them and enough free invigilators who do not teach the course.
 *
 * Input (minutes from midnight, dates "Y-m-d"):
 *  dates:        [date => [start, end]]  the daily window on each candidate date
 *  student_busy: [date => [{start, end, label}]]  exams the linked students already sit
 *  halls:        [{hall_id, hall_name, capacity, busy: [date => [{start, end, seats, shareable}]]}]
 *  invigilators: [{key, name, teaches, busy: [date => [{start, end}]]}]  one entry per person
 *  candidates:   total candidates of all linked specialties
 */
class JointExamSlotFinder
{
    public const STEP_MINUTES = 30;

    /** Per date, why times were refused: [date => [type => [count, message]]]. Filled by options(). */
    private array $refusals = [];

    public function __construct(private array $input) {}

    /**
     * The most common reason times were refused on a date (or on every date
     * when $date is null), or null when nothing was refused.
     */
    public function mainRefusal(?string $date = null): ?string
    {
        $counts = [];
        foreach ($date === null ? $this->refusals : [$this->refusals[$date] ?? []] as $types) {
            foreach ($types as $type => [$count, $message]) {
                $counts[$type] ??= [0, $message];
                $counts[$type][0] += $count;
            }
        }
        if (!$counts) {
            return null;
        }
        uasort($counts, fn(array $a, array $b) => $b[0] <=> $a[0]);

        return reset($counts)[1];
    }

    /** @return array[] every workable time, best first */
    public function options(int $duration, int $step = self::STEP_MINUTES): array
    {
        $options = [];

        foreach ($this->input['dates'] as $date => [$windowStart, $windowEnd]) {
            $first = (int) ceil($windowStart / $step) * $step;
            for ($start = $first; $start + $duration <= $windowEnd; $start += $step) {
                $check = $this->check($date, $start, $start + $duration);
                if ($check['ok']) {
                    $options[] = $check;
                    continue;
                }
                foreach ($check['problems'] as $problem) {
                    $this->refusals[$date][$problem['type']] ??= [0, $problem['message']];
                    $this->refusals[$date][$problem['type']][0]++;
                }
            }
        }

        usort($options, fn(array $a, array $b) => $a['sort'] <=> $b['sort']);

        return array_map(function (array $option) {
            unset($option['sort'], $option['ok'], $option['problems']);
            return $option;
        }, $options);
    }

    /**
     * Whether the joint exam can take place at this time, and why not.
     *
     * @return array{ok: bool, problems: array[], ...}
     */
    public function check(string $date, int $start, int $end): array
    {
        $problems = [];
        $window = $this->input['dates'][$date] ?? null;

        if (!$window) {
            $problems[] = ['type' => 'date', 'message' => 'This date is not inside the exam period of every linked specialty.'];
        } elseif ($start < $window[0] || $end > $window[1]) {
            $problems[] = ['type' => 'hours', 'message' => 'This time is outside the exam hours ' . Time::toTime($window[0]) . ' – ' . Time::toTime($window[1]) . '.'];
        }

        $sameDay = $this->input['student_busy'][$date] ?? [];
        $gap = null;
        foreach ($sameDay as $busy) {
            if (Time::overlaps($start, $end, $busy['start'], $busy['end'])) {
                $problems[] = ['type' => 'students', 'message' => "The students already sit {$busy['label']} then (" . Time::toTime($busy['start']) . ' – ' . Time::toTime($busy['end']) . ').'];
                continue;
            }
            $between = Time::gap($start, $end, $busy['start'], $busy['end']);
            $gap = $gap === null ? $between : min($gap, $between);
        }

        $needed = (int) $this->input['candidates'];
        [$seats, $hallsNeeded] = $this->seats($date, $start, $end, $needed);
        if ($seats < $needed) {
            $problems[] = ['type' => 'seats', 'message' => "Only {$seats} of the {$needed} exam hall seats needed are free."];
        }

        [$free, $teaching] = $this->invigilators($date, $start, $end);
        $invigilatorsNeeded = max(1, $hallsNeeded);
        // With no invigilator assigned to the linked exams yet, there is nothing to check:
        // the exam timetable assigns them when it is generated.
        $checkInvigilators = $this->input['check_invigilators'] ?? true;
        if ($checkInvigilators && $free < $invigilatorsNeeded) {
            $problems[] = ['type' => 'invigilators', 'message' => "Only {$free} of the {$invigilatorsNeeded} invigilators needed are free"
                . ($teaching ? " ({$teaching} more are free but teach the course)." : '.')];
        }

        return [
            'ok' => empty($problems),
            'problems' => $problems,
            'date' => $date,
            'start_time' => Time::toTime($start),
            'end_time' => Time::toTime($end),
            'seats_needed' => $needed,
            'seats_free' => $seats,
            'halls_needed' => $hallsNeeded,
            'invigilators_needed' => $invigilatorsNeeded,
            'invigilators_free' => $free,
            'other_exams_that_day' => count($sameDay),
            'min_break_minutes' => $gap,
            // Days with fewer exams first, then the longest break, then earliest.
            'sort' => [count($sameDay), -($gap ?? PHP_INT_MAX), $date, $start],
        ];
    }

    /** Free seats at the time and how many halls seat everyone (largest first). */
    private function seats(string $date, int $start, int $end, int $needed): array
    {
        $free = [];

        foreach ($this->input['halls'] as $hall) {
            $used = 0;
            foreach ($hall['busy'][$date] ?? [] as $busy) {
                if (!Time::overlaps($start, $end, $busy['start'], $busy['end'])) {
                    continue;
                }
                $sameTimes = $busy['start'] === $start && $busy['end'] === $end;
                $used += ($busy['shareable'] ?? false) && $sameTimes ? (int) $busy['seats'] : (int) $hall['capacity'];
            }
            $seats = max(0, (int) $hall['capacity'] - $used);
            if ($seats > 0) {
                $free[] = $seats;
            }
        }

        rsort($free);
        $seated = 0;
        $halls = 0;
        foreach ($free as $seats) {
            if ($seated >= $needed) {
                break;
            }
            $seated += $seats;
            $halls++;
        }

        return [array_sum($free), $halls];
    }

    /** [free invigilators who may invigilate the course, free ones who teach it] */
    private function invigilators(string $date, int $start, int $end): array
    {
        $free = 0;
        $teaching = 0;

        foreach ($this->input['invigilators'] as $invigilator) {
            foreach ($invigilator['busy'][$date] ?? [] as $busy) {
                if (Time::overlaps($start, $end, $busy['start'], $busy['end'])) {
                    continue 2;
                }
            }
            $invigilator['teaches'] ? $teaching++ : $free++;
        }

        return [$free, $teaching];
    }
}
