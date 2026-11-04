<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseExamSession;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\CourseExamSessionDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * A course that could not get an exam session anywhere. Its own fix is a shorter exam that fits in the
 * free time left on a date; the other fixes come from its obstacles (longer hours, halls, invigilators).
 */
class CourseExamSessionDefinition extends AbstractDefinition
{
    private const MIN_DURATION = 30;

    public function constraintKey(): ?string
    {
        return CourseExamSession::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return CourseExamSessionDiagnostic::class;
    }

    public function priority(): int
    {
        return 40;
    }

    protected function name(): string
    {
        return 'Course exam';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The ' . Text::course($subject, $context) . ' exam could not be scheduled';
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $courseId = $issue->subject->get('course_id');
        $duration = (int) $issue->subject->get('duration');

        if (!$courseId || $duration <= self::MIN_DURATION) {
            return [];
        }

        $longest = collect($context->dates())->map(fn($date) => $context->longestFreeStretch($date))->max() ?? 0;
        $step = $context->context->slotStep();
        $fits = intdiv(min($longest, $duration - 1), $step) * $step;

        if ($fits < self::MIN_DURATION || $fits >= $duration) {
            return [];
        }

        return [new Remedy(
            action: Remedy::MODIFY,
            target: $issue->subject,
            label: 'Shorten the ' . Text::course($issue->subject, $context) . ' exam from ' . Text::minutes($duration) . ' to ' . Text::minutes($fits),
            changes: [['field' => 'duration', 'from' => $duration, 'to' => $fits]],
            result: ['course_id' => $courseId, 'duration' => $fits],
            cost: Cost::CHANGE_SESSION_DURATION + Cost::shift($duration - $fits),
        )];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        if ($step['action'] !== Remedy::MODIFY || empty($step['result']['course_id'])) {
            throw new CannotApplyRemedy('This step has to be done by hand: ' . lcfirst($step['label']) . '.');
        }

        return RequestEdit::upsertException($request, SessionDuration::KEY, 'course_exceptions', 'course_id', $step['result']['course_id'], [
            'duration' => (int) $step['result']['duration'],
        ]);
    }
}
