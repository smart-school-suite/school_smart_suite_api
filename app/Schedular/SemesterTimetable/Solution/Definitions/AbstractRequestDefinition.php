<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions;

use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\SemesterTimetable\Solution\Support\Cost;
use App\Schedular\SemesterTimetable\Solution\Support\RequestEdit;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

abstract class AbstractRequestDefinition extends AbstractDefinition
{
    abstract protected function accepts(Subject $target, SolutionContext $context): callable;

    abstract protected function requestKey(): string;

    abstract protected function identityFields(): array;

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $subject = $edit->subject($step['target']['id']);
        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());

        match ($step['action']) {
            Remedy::MOVE => $entries[$index] = [
                ...$entries[$index],
                'day' => $step['result']['day'],
                'start_time' => $step['result']['start_time'],
                'end_time' => $step['result']['end_time'],
            ],
            Remedy::REMOVE => array_splice($entries, $index, 1),
            default => throw new CannotApplyRemedy('This step has to be done by hand: ' . lcfirst($step['label']) . '.'),
        };

        $request[$this->requestKey()] = array_values($entries);

        return $request;
    }

    public function replaceResource(array $request, Subject $subject, string $field, string $newId): array
    {
        if (!in_array($field, $this->identityFields(), true)) {
            throw new CannotApplyRemedy('This request does not name a ' . str_replace('_id', '', $field) . ', so the replacement has to be made by hand.');
        }

        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());
        $entries[$index][$field] = $newId;
        $request[$this->requestKey()] = $entries;

        return $request;
    }

    protected function occupiesWith(Subject $target): array
    {
        return array_filter([
            'teacher_id' => $target->get('teacher_id'),
            'hall_id' => $target->get('hall_id'),
            'needs_hall' => !$target->get('hall_id'),
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
