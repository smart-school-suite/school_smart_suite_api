<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

abstract class AbstractLimitDefinition extends AbstractDefinition
{
    abstract protected function unit(): string;

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $max = $obstacle->get('max');

        if (!is_numeric($max)) {
            return [];
        }

        $day = $obstacle->day() ?? ($issues[0] ?? null)?->day();
        $newMax = (int) $max + 1;

        return [new Remedy(
            action: Remedy::RELAX,
            target: $obstacle,
            label: 'Allow up to ' . $newMax . ' ' . $this->unit() . ($day ? ' on ' . ucfirst($day) : '') . " (currently {$max})",
            changes: [['field' => 'max', 'from' => (int) $max, 'to' => $newMax]],
            result: array_filter([
                'day' => $day,
                'max' => $newMax,
                'teacher_id' => $obstacle->get('teacher_id'),
                'course_id' => $obstacle->get('course_id'),
                'scope' => $day ? 'day_exception' : null,
            ]),
            cost: Cost::RELAX_LIMIT,
        )];
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'Too many ' . $this->unit() . ' on ' . Text::day($subject->day());
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::day($obstacle->day()) . ' already has the maximum of ' . ($obstacle->get('max') ?? 'the allowed') . ' ' . $this->unit();
    }
}
