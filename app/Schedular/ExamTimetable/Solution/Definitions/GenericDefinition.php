<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Str;

/** Fallback for a key the catalog does not know, so an unhandled constraint never stops path generation. */
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
        return [$this->review($issue->subject, 'Review ' . lcfirst($this->describe($issue->subject, $context)) . ' manually')];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [$this->review($obstacle, 'Review ' . lcfirst($this->describe($obstacle, $context)) . ' manually')];
    }
}
