<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as Constraint;
use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class RequestedAssignmentDefinition extends AbstractRequestDefinition
{
    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Requested exam assignment';
    }

    /** The named halls and invigilators must be free too. */
    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $courseId = $target->get('course_id');
        $hallIds = $target->get('hall_ids', []);
        $invigilatorIds = $target->get('invigilator_ids', []);

        return function (string $date, int $start, int $end) use ($context, $courseId, $hallIds, $invigilatorIds) {
            if (!$courseId || !$context->canHost($courseId, $date, $start, $end)) {
                return false;
            }
            $freeHalls = $context->freeHalls($date, $start, $end, [], $courseId)->pluck('hall_id')->all();
            $freeInvigilators = $context->freeInvigilators($date, $start, $end, $courseId, [], $courseId)->pluck('invigilator_id')->all();

            return !array_diff($hallIds, $freeHalls) && !array_diff($invigilatorIds, $freeInvigilators);
        };
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The requested assignment for ' . Text::course($subject, $context) . ' on ' . Text::when($subject) . ' could not be used';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::course($obstacle, $context)) . ' is assigned to ' . Text::period($obstacle) . ' that day';
    }

    protected function requestKey(): string
    {
        return Constraint::KEY;
    }

    protected function identityFields(): array
    {
        return ['course_id'];
    }

    public function replaceResource(array $request, Subject $subject, string $field, ?string $oldId, string $newId): array
    {
        $entries = array_values($request[$this->requestKey()] ?? []);
        $index = RequestEdit::findEntry($entries, $subject, $this->identityFields());
        $listField = $field === 'hall_id' ? 'hall_ids' : 'invigilator_ids';
        $list = array_values($entries[$index][$listField] ?? []);

        $position = $oldId !== null ? array_search($oldId, $list, true) : false;
        if ($position === false) {
            $list[] = $newId;
        } else {
            $list[$position] = $newId;
        }

        $entries[$index][$listField] = array_values(array_unique($list));
        $request[$this->requestKey()] = $entries;

        return $request;
    }

    public function addHall(array $request, Subject $subject, string $hallId): array
    {
        return $this->replaceResource($request, $subject, 'hall_id', null, $hallId);
    }
}
