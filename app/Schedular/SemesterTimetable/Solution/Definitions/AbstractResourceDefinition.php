<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Collection;

abstract class AbstractResourceDefinition extends AbstractDefinition
{
    abstract protected function field(): string;

    abstract protected function resourceLabel(): string;

    abstract protected function fixedFor(): array;

    abstract protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection;

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $issue = collect($issues)->first(fn(Issue $issue) => $issue->window() !== null && !in_array($issue->type(), $this->fixedFor(), true));

        if (!$issue) {
            return [];
        }

        $current = $issue->subject->get($this->field()) ?? ($obstacle->get('conflict')[$this->field()] ?? null) ?? $obstacle->get($this->field());

        return $this->candidates($issue, $issue->window(), $current, $context)
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn(array $candidate) => $this->replace($obstacle, $issue, $current, $candidate))
            ->values()
            ->all();
    }

    protected function replace(Subject $obstacle, Issue $issue, ?string $current, array $candidate): Remedy
    {
        $field = $this->field();
        $newId = $candidate[$field];
        $name = $candidate['name'] ?? $candidate['teacher_name'] ?? $candidate['hall_name'] ?? $newId;
        [$start, $end] = $issue->window();

        return new Remedy(
            action: Remedy::REPLACE,
            target: $obstacle,
            label: "Use {$this->resourceLabel()} {$name} instead for this class on " . Text::when($issue->subject),
            changes: [['field' => $field, 'from' => $current, 'to' => $newId]],
            result: [$field => $newId, 'name' => $name, 'for_issue_id' => $issue->id()],
            cost: Cost::REPLACE_RESOURCE + intdiv((int) ($candidate['load_minutes'] ?? 0), 600),
            occupies: ['day' => $issue->day(), 'start' => $start, 'end' => $end, $field => $newId],
        );
    }
}
