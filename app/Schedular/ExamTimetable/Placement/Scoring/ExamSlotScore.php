<?php

namespace App\Schedular\ExamTimetable\Placement\Scoring;

use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Start times a session can use on a date, best first.
 *
 * Times step by 15 minutes inside the date's opening hours. A time that
 * overlaps another exam of the same students is never offered. Among the
 * rest, the one leaving the longest break to the other exams of the day
 * comes first; on a day without exams the earliest time comes first.
 *
 * With candidate groups (resit exams) only the exams sharing a candidate with
 * the course count, so exams of different candidates can run side by side,
 * and a time when some of its candidates sit another exam is never offered.
 */
class ExamSlotScore
{
    private const STEP_MINUTES = 15;

    /** @return array<int, array{start_time: string, end_time: string, min_gap_minutes: ?int}> */
    public function suggestSlots(ExamDateDTO $day, int $duration, ?string $courseId = null): array
    {
        $context = RequestContext::fromPayload();
        $dayStart = Time::toMinutes($day->startTime);
        $dayEnd = Time::toMinutes($day->endTime);

        $sessions = $day->allocations;
        if ($courseId !== null && $context->hasCandidateGroups()) {
            $sessions = array_filter($sessions, fn(array $session) => $session['course_id'] !== $courseId
                && $context->sharesCandidates($courseId, $session['course_id']));
        }

        $taken = array_values(array_map(fn(array $session) => [
            'start' => Time::toMinutes($session['start_time']),
            'end' => Time::toMinutes($session['end_time']),
        ], $sessions));

        $blocked = $courseId !== null ? $context->candidateBusyWindows($courseId, $day->date) : [];

        $first = (int) ceil($dayStart / self::STEP_MINUTES) * self::STEP_MINUTES;
        $candidates = [];

        for ($start = $first; $start + $duration <= $dayEnd; $start += self::STEP_MINUTES) {
            $end = $start + $duration;
            $gap = null;

            foreach ($blocked as $window) {
                if (Time::overlaps($start, $end, $window['start'], $window['end'])) {
                    continue 2;
                }
            }

            foreach ($taken as $interval) {
                if (Time::overlaps($start, $end, $interval['start'], $interval['end'])) {
                    continue 2;
                }
                $between = Time::gap($start, $end, $interval['start'], $interval['end']);
                $gap = $gap === null ? $between : min($gap, $between);
            }

            $candidates[] = [
                'start_time' => Time::toTime($start),
                'end_time' => Time::toTime($end),
                'min_gap_minutes' => $gap,
                'sort' => [-($gap ?? 0), $start],
            ];
        }

        usort($candidates, fn(array $a, array $b) => $a['sort'] <=> $b['sort']);

        return array_map(function (array $candidate) {
            unset($candidate['sort']);
            return $candidate;
        }, $candidates);
    }
}
