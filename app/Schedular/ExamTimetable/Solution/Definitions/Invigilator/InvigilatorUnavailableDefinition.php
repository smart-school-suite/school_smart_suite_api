<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\ChangesExamSchedule;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Too few free invigilators who do not teach the course. */
class InvigilatorUnavailableDefinition extends AbstractDefinition
{
    use ChangesExamSchedule;

    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Not enough invigilators';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $teachers = count($obstacle->get('teaching_invigilator_ids', []));
        $when = $obstacle->date() ? ' on ' . Text::when($obstacle) : '';

        return 'Only ' . $obstacle->get('invigilators_free', 0) . ' of the ' . $obstacle->get('invigilators_needed') . " invigilators needed are free{$when}"
            . ($teachers ? " ({$teachers} more are free but teach the course)" : '');
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $remedies = [];
        $ratio = (int) ($context->payload['candidates_per_invigilator'] ?? 0);

        if ($ratio > 0) {
            $remedies[] = new Remedy(
                action: Remedy::RELAX,
                target: $obstacle,
                label: 'Use one invigilator per ' . ($ratio * 2) . " candidates (it is one per {$ratio} now)",
                changes: [['field' => 'candidates_per_invigilator', 'from' => $ratio, 'to' => $ratio * 2]],
                result: ['scope' => 'candidates_per_invigilator', 'candidates_per_invigilator' => $ratio * 2],
                cost: Cost::FEWER_INVIGILATORS,
            );
        }

        $duration = (int) (collect($issues)->map(fn($issue) => $issue->subject->get('duration'))->filter()->first() ?? 0);
        if ($duration) {
            array_push($remedies, ...$this->moreExamTime($obstacle, $context, $duration));
        }

        $missing = max(1, (int) $obstacle->get('invigilators_needed') - (int) $obstacle->get('invigilators_free'));
        $remedies[] = $this->review($obstacle, "Assign at least {$missing} more invigilator(s) to this exam who do not teach " . \App\Schedular\ExamTimetable\Solution\Support\Text::course($obstacle, $context));

        return $remedies;
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        return $this->applySchedule($request, $step);
    }
}
