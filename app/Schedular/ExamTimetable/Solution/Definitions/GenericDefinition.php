<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Str;

/** Used for any key the catalog does not know, so path planning never stops. */
class GenericDefinition extends AbstractDefinition
{
    private string $type = 'constraint';

    public function keys(): array
    {
        return [];
    }

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
        return [$this->review($issue->subject, 'Review ' . lcfirst($this->describe($issue->subject, $context)) . ' by hand')];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [$this->review($obstacle, 'Review ' . lcfirst($this->describe($obstacle, $context)) . ' by hand')];
    }
}
