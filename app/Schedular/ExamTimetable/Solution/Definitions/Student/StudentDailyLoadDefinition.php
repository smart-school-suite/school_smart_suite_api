<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange as Constraint;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** The daily exam range; fixed by changing the range on one date only. */
class StudentDailyLoadDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Daily exam load';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return $subject->get('breach') === 'min'
            ? 'Too few exams on ' . Text::date($subject->date())
            : 'Too many exams on ' . Text::date($subject->date());
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return $obstacle->get('breach') === 'min'
            ? Text::date($obstacle->date()) . ' has ' . $obstacle->get('sessions') . ' exam(s), fewer than the minimum of ' . $obstacle->get('min_sessions')
            : Text::date($obstacle->date()) . ' already has the maximum of ' . $obstacle->get('max_sessions') . ' exam(s)';
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $subject = $issue->subject;
        $sessions = (int) $subject->get('sessions');

        return $subject->get('breach') === 'min'
            ? [$this->relax($subject, $subject->date(), 'min_sessions', (int) $subject->get('min_sessions'), max(0, $sessions))]
            : [$this->relax($subject, $subject->date(), 'max_sessions', (int) $subject->get('max_sessions'), $sessions)];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        if ($obstacle->get('breach') === 'min' || !$obstacle->date()) {
            return [];
        }
        $max = (int) $obstacle->get('max_sessions');
        return [$this->relax($obstacle, $obstacle->date(), 'max_sessions', $max, $max + 1)];
    }

    private function relax(Subject $target, ?string $date, string $field, int $from, int $to): Remedy
    {
        $label = $field === 'max_sessions'
            ? "Allow up to {$to} exam(s) on " . Text::date($date) . " (the maximum is {$from})"
            : "Allow {$to} exam(s) on " . Text::date($date) . " (the minimum is {$from})";

        return new Remedy(
            action: Remedy::RELAX,
            target: $target,
            label: $label,
            changes: [['field' => $field, 'from' => $from, 'to' => $to]],
            result: ['date' => $date, $field => $to],
            cost: Cost::RELAX_LIMIT,
        );
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $result = $step['result'];
        $range = $request[Constraint::KEY] ?? null;

        if (!$range || empty($result['date'])) {
            throw new CannotApplyRemedy('The daily exam range is not set in this version, so there is nothing to change.');
        }

        $current = collect($range['date_exceptions'] ?? [])->firstWhere('date', $result['date']) ?? [];
        $values = [
            'max_sessions' => (int) ($result['max_sessions'] ?? $current['max_sessions'] ?? $range['max_sessions']),
            'min_sessions' => (int) ($result['min_sessions'] ?? $current['min_sessions'] ?? $range['min_sessions'] ?? 0),
        ];
        $values['min_sessions'] = min($values['min_sessions'], $values['max_sessions']);

        return RequestEdit::dateException($request, Constraint::KEY, $result['date'], $values);
    }
}
