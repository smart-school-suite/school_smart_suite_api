<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student\StudentDailyLoadBlocker;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Student\StudentDailyLoadDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** The number of exams students sit on one date. The fix is a date exception to the min/max range. */
class StudentDailyLoadDefinition extends AbstractDefinition
{
    public function constraintKey(): ?string
    {
        return StudentDailyLoadRange::KEY;
    }

    public function violationKey(): ?string
    {
        return StudentDailyLoad::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return StudentDailyLoadDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return StudentDailyLoadBlocker::class;
    }

    protected function name(): string
    {
        return 'Exams per day';
    }

    public function shortName(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'the exams-per-day limit on ' . Text::date($subject->date());
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        $count = (int) $subject->get('session_count');

        return $subject->get('breach') === 'min'
            ? "Only {$count} exam(s) on " . Text::date($subject->date()) . ' (at least ' . $subject->get('min_sessions') . ' wanted)'
            : "{$count} exams on " . Text::date($subject->date()) . ' (at most ' . $subject->get('max_sessions') . ' wanted)';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return $obstacle->get('breach') === 'min'
            ? 'There are not enough exams left to reach the minimum on ' . Text::date($obstacle->date())
            : 'Every course had to be scheduled and the other dates were full or could not take it';
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $date = $obstacle->date();
        $count = (int) $obstacle->get('session_count');

        if (!$date || $count <= 0) {
            return [];
        }

        $field = $obstacle->get('breach') === 'min' ? 'min_sessions' : 'max_sessions';
        $current = (int) $obstacle->get($field);

        return [new Remedy(
            action: Remedy::RELAX,
            target: $obstacle,
            label: ($field === 'max_sessions' ? "Allow up to {$count} exams" : "Allow as few as {$count} exam(s)") . ' on ' . Text::date($date) . " (currently {$current})",
            changes: [['field' => $field, 'from' => $current, 'to' => $count]],
            result: ['date' => $date, $field => $count],
            cost: Cost::RELAX_LIMIT,
        )];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $result = $step['result'] ?? [];

        if ($step['action'] !== Remedy::RELAX || empty($result['date'])) {
            return parent::applyToRequest($request, $step, $edit);
        }

        $range = $request[StudentDailyLoadRange::KEY] ?? [];
        $values = array_intersect_key($result, array_flip(['min_sessions', 'max_sessions']));
        $defaults = [
            'max_sessions' => (int) ($range['max_sessions'] ?? $result['max_sessions'] ?? 1),
            'min_sessions' => (int) ($range['min_sessions'] ?? 1),
        ];

        $request = RequestEdit::upsertException($request, StudentDailyLoadRange::KEY, 'date_exceptions', 'date', $result['date'], $values, $defaults);

        // Keep the exception valid: min never above max.
        $request[StudentDailyLoadRange::KEY]['date_exceptions'] = collect($request[StudentDailyLoadRange::KEY]['date_exceptions'])
            ->map(fn($exception) => [...$exception, 'min_sessions' => min((int) $exception['min_sessions'], (int) $exception['max_sessions'])])
            ->all();

        return $request;
    }
}
