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
 * A "put X at this date and time" request. It can be moved to the nearest
 * time that works or removed; as an obstacle it moves out of the way of the
 * request it blocks.
 */
abstract class AbstractRequestDefinition extends AbstractDefinition
{
    /** @return callable(string $date, int $start, int $end): bool */
    abstract protected function accepts(Subject $target, SolutionContext $context): callable;

    /** Key of the request entries in the generation request. */
    abstract protected function requestKey(): string;

    /** Fields that identify the entry besides its date and times. */
    abstract protected function identityFields(): array;

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $target = $issue->subject;

        return [
            ...$this->moves($target, $context, $this->accepts($target, $context), Cost::MOVE_REQUEST),
            $this->remove($target, Cost::REMOVE_REQUEST, context: $context),
        ];
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [
            ...$this->moves($obstacle, $context, $this->accepts($obstacle, $context), Cost::MOVE_OTHER_REQUEST, $this->issueWindows($issues)),
            $this->remove($obstacle, Cost::REMOVE_REQUEST, context: $context),
        ];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $subject = $edit->subject($step['target']['id']);
        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());

        switch ($step['action']) {
            case Remedy::MOVE:
                $entries[$index] = [
                    ...$entries[$index],
                    'date' => $step['result']['date'],
                    'start_time' => $step['result']['start_time'],
                    'end_time' => $step['result']['end_time'],
                ];
                break;
            case Remedy::REMOVE:
                array_splice($entries, $index, 1);
                break;
            default:
                return parent::applyToRequest($request, $step, $edit);
        }

        $request[$this->requestKey()] = array_values($entries);

        return $request;
    }

    /** Swaps a hall or invigilator named by the request (used by resource definitions). */
    public function replaceResource(array $request, Subject $subject, string $field, ?string $oldId, string $newId): array
    {
        throw new CannotApplyRemedy('This request does not name a ' . str_replace('_id', '', $field) . ', so it has to be changed by hand.');
    }

    /** Adds a hall to the request (used when its halls are too small). */
    public function addHall(array $request, Subject $subject, string $hallId): array
    {
        throw new CannotApplyRemedy('This request does not name halls, so it has to be changed by hand.');
    }
}
