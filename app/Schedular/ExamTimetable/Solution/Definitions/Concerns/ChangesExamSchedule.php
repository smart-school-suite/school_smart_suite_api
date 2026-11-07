<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Concerns;

use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * Changes to when exams can take place: longer opening hours on a date,
 * exams on a date that was excluded, the default hours back on a date, or a
 * different exam length for a course. Every remedy carries a 'scope' in its
 * result, which applySchedule() turns into a change of the request.
 */
trait ChangesExamSchedule
{
    protected const LATEST_END = 22 * 60;

    protected function extendHours(Subject $target, SolutionContext $context, string $date, int $start, int $end): ?Remedy
    {
        $current = $context->window($date);
        if (!$current || $start < 0 || $end > self::LATEST_END || ($start >= $current[0] && $end <= $current[1])) {
            return null;
        }

        $newStart = min($start, $current[0]);
        $newEnd = max($end, $current[1]);
        $from = Text::time(Time::toTime($current[0]), Time::toTime($current[1]));
        $to = Text::time(Time::toTime($newStart), Time::toTime($newEnd));

        return new Remedy(
            action: Remedy::MODIFY,
            target: $target,
            label: 'Open ' . Text::date($date) . " for exams from {$to} (it is {$from} now)",
            changes: [
                ['field' => 'start_time', 'from' => Time::toTime($current[0]), 'to' => Time::toTime($newStart)],
                ['field' => 'end_time', 'from' => Time::toTime($current[1]), 'to' => Time::toTime($newEnd)],
            ],
            result: ['scope' => 'date_hours', 'date' => $date, 'start_time' => Time::toTime($newStart), 'end_time' => Time::toTime($newEnd)],
            cost: Cost::EXTEND_OPENING_HOURS + Cost::shift(($current[0] - $newStart) + ($newEnd - $current[1])),
        );
    }

    /** @return Remedy[] one per excluded date of the exam period, nearest first */
    protected function allowExcludedDates(Subject $target, SolutionContext $context, ?string $near = null, int $limit = 2): array
    {
        $input = $context->input();
        $excluded = array_values(array_filter($input->dateExclusions(), fn(string $date) => $input->isWithinExamPeriod($date)));

        if ($near) {
            usort($excluded, fn(string $a, string $b) => abs(strtotime($a) - strtotime($near)) <=> abs(strtotime($b) - strtotime($near)));
        }

        return array_map(fn(string $date) => new Remedy(
            action: Remedy::RELAX,
            target: $target,
            label: 'Allow exams on ' . Text::date($date) . ' (it is excluded now)',
            changes: [['field' => 'date_exclusions', 'from' => $date, 'to' => null]],
            result: ['scope' => 'allow_date', 'date' => $date],
            cost: Cost::ALLOW_EXCLUDED_DATE,
        ), array_slice($excluded, 0, $limit));
    }

    /** More time for an exam of $duration minutes: the least busy date opened longer, or an excluded date allowed. */
    protected function moreExamTime(Subject $target, SolutionContext $context, int $duration): array
    {
        $dates = $context->dates();
        usort($dates, fn(string $a, string $b) => [count($context->sessions($a)), $a] <=> [count($context->sessions($b)), $b]);

        $remedies = [];
        foreach ($dates as $date) {
            $window = $context->window($date);
            if ($window && ($remedy = $this->extendHours($target, $context, $date, $window[0], $window[1] + $duration))) {
                $remedies[] = $remedy;
                break;
            }
        }

        return [...$remedies, ...$this->allowExcludedDates($target, $context)];
    }

    protected function courseDuration(Subject $target, SolutionContext $context, string $courseId, int $minutes): Remedy
    {
        $current = $context->input()->sessionDurationCourse($courseId);

        return new Remedy(
            action: Remedy::MODIFY,
            target: $target,
            label: 'Make the ' . ($context->courseName($courseId) ?? 'course') . " exam last {$minutes} minutes (it is {$current} now)",
            changes: [['field' => 'duration', 'from' => $current, 'to' => $minutes]],
            result: ['scope' => 'course_duration', 'course_id' => $courseId, 'duration' => $minutes],
            cost: Cost::CHANGE_DURATION + Cost::shift($current - $minutes),
        );
    }

    /** @throws CannotApplyRemedy when the step is not a schedule change */
    protected function applySchedule(array $request, array $step): array
    {
        $result = $step['result'] ?? [];
        $key = OperationalPeriod::KEY;

        switch ($result['scope'] ?? null) {
            case 'date_hours':
                return RequestEdit::dateException($request, $key, $result['date'], [
                    'start_time' => $result['start_time'],
                    'end_time' => $result['end_time'],
                ]);

            case 'default_hours':
                $request[$key]['date_exceptions'] = array_values(array_filter(
                    $request[$key]['date_exceptions'] ?? [],
                    fn(array $exception) => ($exception['date'] ?? null) !== $result['date']
                ));
                return $request;

            case 'allow_date':
                $request[$key]['date_exclusions'] = array_values(array_diff($request[$key]['date_exclusions'] ?? [], (array) $result['date']));
                return $request;

            case 'allow_dates':
                $request[$key]['date_exclusions'] = array_values(array_diff($request[$key]['date_exclusions'] ?? [], $result['dates']));
                return $request;

            case 'course_duration':
                $exceptions = collect($request['session_duration']['course_exceptions'] ?? [])
                    ->reject(fn(array $exception) => ($exception['course_id'] ?? null) === $result['course_id'])
                    ->values()
                    ->push(['course_id' => $result['course_id'], 'duration' => (int) $result['duration']])
                    ->all();
                $request['session_duration']['course_exceptions'] = $exceptions;
                return $request;

            case 'candidates_per_invigilator':
                $request['candidates_per_invigilator'] = (int) $result['candidates_per_invigilator'];
                return $request;
        }

        throw new CannotApplyRemedy('This step has to be done by hand: ' . lcfirst(rtrim($step['label'] ?? 'review it', '.')) . '.');
    }
}
