<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration as SessionDurationViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\SessionDurationBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** A requested exam length that differs from the course's exam duration. */
class SessionDurationDefinition extends AbstractDefinition
{
    public function violationKey(): ?string
    {
        return SessionDurationViolation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return SessionDurationBlocker::class;
    }

    public function priority(): int
    {
        return 20;
    }

    protected function name(): string
    {
        return 'Exam duration';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The ' . Text::course($obstacle, $context) . ' exam lasts ' . Text::minutes((int) $obstacle->get('duration'))
            . ', but ' . Text::minutes((int) $obstacle->get('requested_duration')) . ' was requested';
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $courseId = $obstacle->get('course_id');
        $requested = (int) $obstacle->get('requested_duration');

        if (!$courseId || $requested <= 0) {
            return [];
        }

        return [new Remedy(
            action: Remedy::MODIFY,
            target: $obstacle,
            label: 'Make the ' . Text::course($obstacle, $context) . ' exam ' . Text::minutes($requested) . ' long (currently ' . Text::minutes((int) $obstacle->get('duration')) . ')',
            changes: [['field' => 'duration', 'from' => (int) $obstacle->get('duration'), 'to' => $requested]],
            result: ['course_id' => $courseId, 'duration' => $requested],
            cost: Cost::CHANGE_SESSION_DURATION,
        )];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        if ($step['action'] !== Remedy::MODIFY || empty($step['result']['course_id'])) {
            return parent::applyToRequest($request, $step, $edit);
        }

        return RequestEdit::upsertException($request, SessionDuration::KEY, 'course_exceptions', 'course_id', $step['result']['course_id'], [
            'duration' => (int) $step['result']['duration'],
        ]);
    }
}
