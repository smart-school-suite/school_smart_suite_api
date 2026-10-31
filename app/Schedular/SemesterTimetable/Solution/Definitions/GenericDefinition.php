<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Str;

class GenericDefinition extends AbstractDefinition
{
    private string $type = 'constraint';

    public function for(string $type): self
    {
        $definition = clone $this;
        $definition->type = $type;

        return $definition;
    }

    protected function name(): string
    {
        return Str::of($this->type)->replace('_', ' ')->replace(' violation', '')->ucfirst()->toString();
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return [$this->remove($issue->subject, Cost::REMOVE_REQUEST + Cost::RELAX_LIMIT, 'Relax or remove ' . lcfirst($this->describe($issue->subject, $context)))];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [new Remedy(
            action: Remedy::REVIEW,
            target: $obstacle,
            label: 'Review ' . lcfirst($this->describe($obstacle, $context)) . ' manually',
            changes: [],
            result: ['manual' => true],
            cost: Cost::MANUAL_REVIEW,
        )];
    }
}
