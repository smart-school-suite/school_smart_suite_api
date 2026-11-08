<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Collection;

/**
 * "This hall / invigilator can't be used" obstacles. When the blocked request
 * names the resource (a requested assignment), another free one is offered.
 */
abstract class AbstractResourceDefinition extends AbstractDefinition
{
    abstract protected function field(): string;

    abstract protected function resourceLabel(): string;

    /** Request types whose resource is the point of the request, so it is never swapped. */
    abstract protected function fixedFor(): array;

    abstract protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection;

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $issue = collect($issues)->first(fn(Issue $issue) => $issue->window() !== null
            && $issue->subject->date()
            && !in_array($issue->type(), $this->fixedFor(), true));

        if (!$issue) {
            return [];
        }

        $current = $obstacle->get($this->field());

        return $this->candidates($issue, $issue->window(), $current, $context)
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn(array $candidate) => $this->replace($obstacle, $issue, $current, $candidate, $context))
            ->values()
            ->all();
    }

    protected function replace(Subject $obstacle, Issue $issue, ?string $current, array $candidate, SolutionContext $context): Remedy
    {
        $field = $this->field();
        $newId = $candidate[$field];
        $name = $candidate['name'] ?? $candidate['hall_name'] ?? $newId;
        $currentName = $current ? ($field === 'hall_id' ? $context->hallName($current) : $context->invigilatorName($current)) : null;

        return new Remedy(
            action: Remedy::REPLACE,
            target: $obstacle,
            label: "Use {$this->resourceLabel()} {$name}" . ($currentName ? " instead of {$currentName}" : '')
                . ' for ' . ($context->courseName($issue->subject->get('course_id') ?? '') ?? 'this exam'),
            changes: [['field' => $field, 'from' => $current, 'to' => $newId]],
            result: [$field => $newId, 'replaces' => $current, 'name' => $name, 'for_issue_id' => $issue->id()],
            cost: Cost::REPLACE_RESOURCE,
        );
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        if ($step['action'] !== Remedy::REPLACE) {
            return parent::applyToRequest($request, $step, $edit);
        }

        $issue = $edit->subject($step['result']['for_issue_id'] ?? '');
        $definition = $edit->definition($issue->type);

        if (!$definition instanceof AbstractRequestDefinition) {
            throw new CannotApplyRemedy('The ' . $this->resourceLabel() . ' for this exam is chosen by the scheduler, so it cannot be swapped automatically.');
        }

        return $definition->replaceResource($request, $issue, $this->field(), $step['result']['replaces'] ?? null, $step['result'][$this->field()]);
    }
}
