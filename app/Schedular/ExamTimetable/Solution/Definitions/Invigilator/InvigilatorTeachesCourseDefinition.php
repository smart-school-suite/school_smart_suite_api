<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorTeachesCourse as Violation;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * Nobody invigilates a course they teach. The fix is never to allow it:
 * another invigilator is offered instead, or more invigilators are needed.
 */
class InvigilatorTeachesCourseDefinition extends InvigilatorBusyDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Invigilator teaches the course';
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $replacements = parent::clearObstacle($obstacle, $issues, $context);

        return $replacements ?: [$this->review(
            $obstacle,
            'Assign an invigilator to this exam who does not teach ' . Text::course($obstacle, $context)
        )];
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $ids = $obstacle->get('invigilator_ids') ?? array_filter([$obstacle->get('invigilator_id')]);
        $names = array_map(fn(string $id) => $context?->invigilatorName($id) ?? 'an invigilator', $ids);
        $who = count($names) > 1 ? Text::list($names) . ' teach' : (($names[0] ?? 'The invigilator') . ' teaches');

        return ucfirst($who) . ' ' . Text::course($obstacle, $context) . ', so they cannot invigilate its exam';
    }
}
