<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

/**
 * "This hall / invigilator cannot be used" obstacles on a request that names one.
 * The fix swaps the named hall or invigilator for a free one, or lets the scheduler choose.
 */
abstract class AbstractResourceDefinition extends AbstractDefinition
{
    abstract protected function field(): string;

    abstract protected function resourceLabel(): string;

    /** Free replacements for the request's window. */
    abstract protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection;

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $field = $this->field();
        $issue = collect($issues)->first(fn(Issue $issue) => $issue->window() !== null && $issue->subject->get($field));

        if (!$issue) {
            return [];
        }

        $current = $issue->subject->get($field);

        return [
            ...$this->candidates($issue, $issue->window(), $current, $context)
                ->take(SolutionContext::MAX_ALTERNATIVES)
                ->map(fn(array $candidate) => $this->replace($obstacle, $issue, $current, $candidate[$field], $candidate['name'] ?? $candidate['hall_name'] ?? $candidate[$field]))
                ->values()
                ->all(),
            new Remedy(
                action: Remedy::MODIFY,
                target: $obstacle,
                label: "Let the scheduler choose the {$this->resourceLabel()} for " . lcfirst($this->requestName($issue, $context)),
                changes: [['field' => $field, 'from' => $current, 'to' => null]],
                result: [$field => null, 'for_issue_id' => $issue->id()],
                cost: Cost::LET_SCHEDULER_CHOOSE,
            ),
        ];
    }

    protected function replace(Subject $obstacle, Issue $issue, ?string $current, string $newId, string $name): Remedy
    {
        $field = $this->field();
        [$start, $end] = $issue->window();

        return new Remedy(
            action: Remedy::REPLACE,
            target: $obstacle,
            label: "Use {$this->resourceLabel()} {$name} instead on " . Text::when($issue->subject),
            changes: [['field' => $field, 'from' => $current, 'to' => $newId]],
            result: [$field => $newId, 'name' => $name, 'for_issue_id' => $issue->id()],
            cost: Cost::REPLACE_RESOURCE,
            occupies: ['date' => $issue->date(), 'start' => $start, 'end' => $end, $field => $newId],
        );
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        if (!in_array($step['action'], [Remedy::REPLACE, Remedy::MODIFY], true)) {
            return parent::applyToRequest($request, $step, $edit);
        }

        $issue = $edit->subject($step['result']['for_issue_id'] ?? '');
        $definition = $edit->definition($issue->type);

        if (!$definition instanceof AbstractRequestDefinition) {
            throw new CannotApplyRemedy("The {$this->resourceLabel()} for this exam is chosen by the scheduler, so it cannot be swapped automatically.");
        }

        return $definition->replaceResource($request, $issue, $this->field(), $step['result'][$this->field()] ?? null);
    }

    private function requestName(Issue $issue, SolutionContext $context): string
    {
        return 'the ' . Text::course($issue->subject, $context, 'requested') . ' exam on ' . Text::when($issue->subject);
    }
}
