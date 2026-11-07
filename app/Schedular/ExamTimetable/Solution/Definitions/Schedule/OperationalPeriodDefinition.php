<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod as Constraint;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod as Violation;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\ChangesExamSchedule;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class OperationalPeriodDefinition extends AbstractDefinition
{
    use ChangesExamSchedule;

    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Exam hours';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return $subject->date()
            ? 'The exam hours on ' . Text::date($subject->date()) . ' are not valid'
            : 'The exam period has no dates exams can take place on';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $allowed = $obstacle->get('allowed_time');
        $period = $obstacle->get('allowed_period');

        return match ($obstacle->get('reason')) {
            'outside_exam_period' => Text::date($obstacle->get('date')) . ' is outside the exam period'
                . ($period ? ' (' . Text::date($period['start_date'] ?? null) . ' to ' . Text::date($period['end_date'] ?? null) . ')' : ''),
            'date_excluded_from_operations' => 'No exams are allowed on ' . Text::date($obstacle->get('date')),
            'invalid_exam_period' => 'The exam has no valid start and end dates',
            'no_exam_dates' => 'Every date of the exam period is excluded',
            'duration_longer_than_opening_hours' => 'The longest exam day only has ' . $obstacle->get('longest_opening_minutes') . ' minutes',
            'no_time_left' => 'No date has a free time long enough for this exam',
            'invalid_duration' => 'The exam length is not valid',
            default => 'Exams on ' . Text::date($obstacle->get('date')) . ' can only run '
                . ($allowed ? Text::time($allowed['start_time'] ?? null, $allowed['end_time'] ?? null) : 'within the opening hours'),
        };
    }

    /** The hard failure itself (bad hours on a date, no exam dates). */
    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $remedies = [];
        foreach ($issue->obstacles as $obstacle) {
            array_push($remedies, ...$this->clearObstacle($obstacle, [$issue], $context));
        }
        return $remedies;
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $date = $obstacle->get('date') ?? $obstacle->conflict('date');
        $start = $obstacle->conflict('start_time');
        $end = $obstacle->conflict('end_time');

        switch ($obstacle->get('reason')) {
            case 'date_excluded_from_operations':
                return $date ? [new Remedy(
                    action: Remedy::RELAX,
                    target: $obstacle,
                    label: 'Allow exams on ' . Text::date($date) . ' (it is excluded now)',
                    changes: [['field' => 'date_exclusions', 'from' => $date, 'to' => null]],
                    result: ['scope' => 'allow_date', 'date' => $date],
                    cost: Cost::ALLOW_EXCLUDED_DATE,
                )] : [];

            case 'no_exam_dates':
                $dates = $obstacle->get('excluded_dates', []);
                return $dates ? [new Remedy(
                    action: Remedy::RELAX,
                    target: $obstacle,
                    label: 'Allow exams on the excluded dates of the exam period',
                    changes: [['field' => 'date_exclusions', 'from' => implode(', ', $dates), 'to' => null]],
                    result: ['scope' => 'allow_dates', 'dates' => $dates],
                    cost: Cost::ALLOW_EXCLUDED_DATE,
                )] : [];

            case 'invalid_operational_hour':
                if ($date && $start && $end) {
                    $remedy = $this->extendHours($obstacle, $context, $date, Time::toMinutes($start), Time::toMinutes($end));
                    return $remedy ? [$remedy] : [];
                }
                // The date's own hours are invalid (a reversed date exception).
                return $date ? [new Remedy(
                    action: Remedy::MODIFY,
                    target: $obstacle,
                    label: 'Use the normal exam hours on ' . Text::date($date),
                    changes: [],
                    result: ['scope' => 'default_hours', 'date' => $date],
                    cost: Cost::EXTEND_OPENING_HOURS,
                )] : [];

            case 'duration_longer_than_opening_hours':
                $issue = collect($issues)->first(fn(Issue $issue) => $issue->subject->get('duration'));
                $window = $date ? $context->window($date) : null;
                if (!$issue || !$window) {
                    return [];
                }
                $remedy = $this->extendHours($obstacle, $context, $date, $window[0], $window[0] + (int) $issue->subject->get('duration'));
                return $remedy ? [$remedy] : [];

            case 'no_time_left':
                return $this->moreExamTime($obstacle, $context, (int) $obstacle->get('duration', 0));

            case 'outside_exam_period':
            case 'invalid_exam_period':
                return [$this->review($obstacle, 'Change the start and end dates of the exam' . ($date ? ' to include ' . Text::date($date) : ''))];
        }

        return [];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        return $this->applySchedule($request, $step);
    }
}
