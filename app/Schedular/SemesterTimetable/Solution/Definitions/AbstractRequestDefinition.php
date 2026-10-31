<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

abstract class AbstractRequestDefinition extends AbstractDefinition
{
    abstract protected function accepts(Subject $target, SolutionContext $context): callable;

    protected function occupiesWith(Subject $target): array
    {
        return array_filter([
            'teacher_id' => $target->get('teacher_id'),
            'hall_id' => $target->get('hall_id'),
        ]);
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $target = $issue->subject;

        return [
            ...$this->moves($target, $context, $this->accepts($target, $context), Cost::MOVE_REQUEST, occupiesWith: $this->occupiesWith($target)),
            $this->remove($target, Cost::REMOVE_REQUEST, context: $context),
        ];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [
            ...$this->moves(
                $obstacle,
                $context,
                $this->accepts($obstacle, $context),
                Cost::MOVE_OTHER_REQUEST,
                avoid: $this->issueWindows($issues),
                occupiesWith: $this->occupiesWith($obstacle),
            ),
            $this->remove($obstacle, Cost::REMOVE_REQUEST, context: $context),
        ];
    }
}
