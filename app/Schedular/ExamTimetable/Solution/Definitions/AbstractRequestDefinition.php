<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

/**
 * "Put this exam (or this hall / invigilator) at this date and time" requests.
 * Moving and removing are handled here; subclasses say which windows are acceptable.
 */
abstract class AbstractRequestDefinition extends AbstractDefinition
{
    abstract protected function accepts(Subject $target, SolutionContext $context): callable;

    abstract protected function requestKey(): string;

    abstract protected function identityFields(): array;

    /** Whether a moved request takes up the students' time (course exams do, hall/invigilator requests do not). */
    protected function occupiesStudents(): bool
    {
        return true;
    }

    /** Length a moved request should have (course exams keep their configured duration). */
    protected function length(Subject $target, SolutionContext $context): ?int
    {
        $courseId = $target->get('course_id');

        return $courseId && $context->context->course($courseId) ? $context->context->duration($courseId) : null;
    }

    protected function occupiesWith(Subject $target): array
    {
        return array_filter([
            'students' => $this->occupiesStudents(),
            'hall_id' => $target->get('hall_id'),
            'invigilator_id' => $target->get('invigilator_id'),
        ]);
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $target = $issue->subject;

        return [
            ...$this->moves($target, $context, $this->accepts($target, $context), Cost::MOVE_REQUEST, length: $this->length($target, $context), occupiesWith: $this->occupiesWith($target)),
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
                length: $this->length($obstacle, $context),
                occupiesWith: $this->occupiesWith($obstacle),
            ),
            $this->remove($obstacle, Cost::REMOVE_REQUEST, context: $context),
        ];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $subject = $edit->subject($step['target']['id']);
        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());

        match ($step['action']) {
            Remedy::MOVE => $entries[$index] = [
                ...$entries[$index],
                'date' => $step['result']['date'],
                'start_time' => $step['result']['start_time'],
                'end_time' => $step['result']['end_time'],
            ],
            Remedy::REMOVE => array_splice($entries, $index, 1),
            default => throw new CannotApplyRemedy('This step has to be done by hand: ' . lcfirst($step['label']) . '.'),
        };

        $request[$this->requestKey()] = array_values($entries);

        if (empty($request[$this->requestKey()])) {
            unset($request[$this->requestKey()]);
        }

        return $request;
    }

    /** Swaps (or clears, with null) the hall or invigilator named by one entry. */
    public function replaceResource(array $request, Subject $subject, string $field, ?string $newId): array
    {
        if (!in_array($field, $this->identityFields(), true)) {
            throw new CannotApplyRemedy('This request does not name a ' . str_replace('_id', '', $field) . ', so the replacement has to be made by hand.');
        }

        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());

        if ($newId === null) {
            unset($entries[$index][$field]);
        } else {
            $entries[$index][$field] = $newId;
        }

        $request[$this->requestKey()] = $entries;

        return $request;
    }
}
