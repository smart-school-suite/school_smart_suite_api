<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorUnavailableBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Nobody free to invigilate. Invigilators are assigned to the exam by hand. */
class InvigilatorUnavailableDefinition extends AbstractDefinition
{
    public function violationKey(): ?string
    {
        return InvigilatorUnavailable::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return InvigilatorUnavailableBlocker::class;
    }

    protected function name(): string
    {
        return 'Invigilators';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        if ($obstacle->get('reason') === 'not_assigned_to_exam') {
            return Text::invigilator($obstacle, $context, 'This person') . ' is not an invigilator of this exam';
        }

        $when = $obstacle->get('dates') && count($obstacle->get('dates')) > 1
            ? 'on any of ' . count($obstacle->get('dates')) . ' dates'
            : 'on ' . Text::date($obstacle->date()) . ($obstacle->get('start_time') ? ' ' . Text::period($obstacle) : '');

        return 'Not enough invigilators are free ' . $when;
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [$this->review($obstacle, $obstacle->get('reason') === 'not_assigned_to_exam'
            ? 'Add ' . Text::invigilator($obstacle, $context, 'this person') . ' to the invigilators of this exam'
            : 'Assign more invigilators to this exam', ['fix_in' => '/exam-invigilators'])];
    }
}
