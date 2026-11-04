<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod as OperationalPeriodViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\OperationalPeriodBlocker;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * Opening hours and exam dates. Fixes add a date exception with longer hours, or reopen an excluded date.
 * Exam start and end dates belong to the exam itself, so changing them is a manual step.
 */
class OperationalPeriodDefinition extends AbstractDefinition
{
    public const NO_FREE_TIME = 'no_free_time';
    public const NO_EXAM_DATES = 'no_exam_dates';

    public function violationKey(): ?string
    {
        return OperationalPeriodViolation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return OperationalPeriodBlocker::class;
    }

    public function priority(): int
    {
        return 10;
    }

    protected function name(): string
    {
        return 'Exam hours';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $date = Text::date($obstacle->date());

        return match ($obstacle->get('reason')) {
            OperationalPeriodValidator::NOT_EXAM_DATE => "{$date} is outside the exam dates (" . Text::date($obstacle->get('allowed_start_date')) . ' to ' . Text::date($obstacle->get('allowed_end_date')) . ')',
            OperationalPeriodValidator::EXCLUDED_DATE => "No exams are held on {$date}",
            OperationalPeriodValidator::OUTSIDE_HOURS => "Exams on {$date} run from " . Text::time($obstacle->get('start_time'), $obstacle->get('end_time')) . ' only',
            self::NO_FREE_TIME => 'There is no free ' . Text::minutes((int) $obstacle->get('duration')) . ' left for it on ' . $this->dateList($obstacle),
            self::NO_EXAM_DATES => 'The exam has no dates on which exams can be held',
            default => 'It falls outside the exam hours',
        };
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return match ($obstacle->get('reason')) {
            OperationalPeriodValidator::OUTSIDE_HOURS => $this->extendForRequest($obstacle, $context),
            self::NO_FREE_TIME => $this->extendForDuration($obstacle, $issues, $context),
            OperationalPeriodValidator::EXCLUDED_DATE => [new Remedy(
                action: Remedy::MODIFY,
                target: $obstacle,
                label: 'Hold exams on ' . Text::date($obstacle->date()) . ' as well',
                changes: [['field' => 'excluded_dates', 'from' => $obstacle->date(), 'to' => null]],
                result: ['date' => $obstacle->date(), 'reopen' => true],
                cost: Cost::REOPEN_DATE,
            )],
            default => [$this->review($obstacle, 'Change the exam dates so that they include ' . Text::date($obstacle->get('conflict')['date'] ?? $obstacle->date()))],
        };
    }

    private function extendForRequest(Subject $obstacle, SolutionContext $context): array
    {
        $date = $obstacle->date();
        $conflict = $obstacle->get('conflict', []);
        $open = Time::toMinutes($obstacle->get('start_time'));
        $close = Time::toMinutes($obstacle->get('end_time'));
        $start = Time::toMinutes($conflict['start_time'] ?? null);
        $end = Time::toMinutes($conflict['end_time'] ?? null);

        if (!$date || $open === null || $close === null || $start === null || $end === null) {
            return [];
        }

        return [$this->hours($obstacle, $date, [$open, $close], [min($open, $start), max($close, $end)])];
    }

    /** Longer hours on one date, long enough for every course this obstacle keeps out. */
    private function extendForDuration(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $gap = $context->context->sessionGap();
        $duration = collect($issues)
            ->map(fn(Issue $issue) => (int) ($issue->subject->get('duration') ?? $obstacle->get('duration')) + $gap)
            ->sum() ?: (int) $obstacle->get('duration') + $gap;

        return collect($obstacle->get('dates', [$obstacle->date()]))
            ->filter()
            ->map(function (string $date) use ($obstacle, $context, $duration) {
                [$open, $close] = $context->context->hoursFor($date);

                return $close + $duration < Time::DAY_END ? $this->hours($obstacle, $date, [$open, $close], [$open, $close + $duration]) : null;
            })
            ->filter()
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->values()
            ->all();
    }

    private function hours(Subject $obstacle, string $date, array $from, array $to): Remedy
    {
        return new Remedy(
            action: Remedy::MODIFY,
            target: $obstacle,
            label: 'Run exams from ' . Text::time(Time::toTime($to[0]), Time::toTime($to[1])) . ' on ' . Text::date($date) . ' (currently ' . Text::time(Time::toTime($from[0]), Time::toTime($from[1])) . ')',
            changes: [
                ['field' => 'start_time', 'from' => Time::toTime($from[0]), 'to' => Time::toTime($to[0])],
                ['field' => 'end_time', 'from' => Time::toTime($from[1]), 'to' => Time::toTime($to[1])],
            ],
            result: ['date' => $date, 'start_time' => Time::toTime($to[0]), 'end_time' => Time::toTime($to[1])],
            cost: Cost::EXTEND_OPERATIONAL_PERIOD + Cost::shift(($from[0] - $to[0]) + ($to[1] - $from[1]), 30),
        );
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $result = $step['result'] ?? [];

        if ($step['action'] !== Remedy::MODIFY || empty($result['date'])) {
            return parent::applyToRequest($request, $step, $edit);
        }

        if (!empty($result['reopen'])) {
            $excluded = collect($request[OperationalPeriod::KEY]['excluded_dates'] ?? [])
                ->reject(fn($date) => Time::date($date) === $result['date'])
                ->values();

            if ($excluded->count() === count($request[OperationalPeriod::KEY]['excluded_dates'] ?? [])) {
                throw new CannotApplyRemedy('This date is closed by a weekday rule, so it has to be opened by hand.');
            }

            $request[OperationalPeriod::KEY]['excluded_dates'] = $excluded->all();

            return $request;
        }

        return RequestEdit::upsertException($request, OperationalPeriod::KEY, 'date_exceptions', 'date', $result['date'], [
            'start_time' => $result['start_time'],
            'end_time' => $result['end_time'],
        ]);
    }

    private function dateList(Subject $obstacle): string
    {
        $dates = $obstacle->get('dates', [$obstacle->date()]);

        return count($dates) > 2
            ? 'any of the ' . count($dates) . ' exam dates'
            : implode(' or ', array_map(fn($date) => Text::date($date), $dates));
    }
}
