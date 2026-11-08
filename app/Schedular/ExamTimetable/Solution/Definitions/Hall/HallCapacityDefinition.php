<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** The halls named by a requested assignment have too few seats: add free halls to it. */
class HallCapacityDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Not enough seats';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The requested halls seat ' . $obstacle->get('seats_free', $obstacle->get('total_capacity'))
            . ' candidate(s) but ' . $obstacle->get('total_candidates') . ' sit the exam';
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $issue = collect($issues)->first(fn(Issue $issue) => $issue->window() && $issue->subject->date());
        if (!$issue) {
            return [];
        }

        $missing = (int) $obstacle->get('total_candidates') - (int) $obstacle->get('seats_free', $obstacle->get('total_capacity'));
        $halls = $context->freeHalls($issue->subject->date(), $issue->window()[0], $issue->window()[1], $obstacle->get('hall_ids', []), $issue->subject->get('course_id'));

        $added = [];
        foreach ($halls as $hall) {
            if ($missing <= 0) {
                break;
            }
            $added[] = $hall;
            $missing -= $hall['seats'];
        }

        if ($missing > 0 || empty($added)) {
            return [];
        }

        $names = array_map(fn(array $hall) => $hall['hall_name'] ?? $hall['hall_id'], $added);

        return [new Remedy(
            action: Remedy::MODIFY,
            target: $obstacle,
            label: 'Add ' . Text::list($names) . ' to the assignment so every candidate has a seat',
            changes: [['field' => 'hall_ids', 'from' => null, 'to' => implode(', ', $names)]],
            result: ['hall_ids' => array_column($added, 'hall_id'), 'for_issue_id' => $issue->id()],
            cost: Cost::ADD_HALL * count($added),
        )];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        $issue = $edit->subject($step['result']['for_issue_id'] ?? '');
        $definition = $edit->definition($issue->type);

        if (!$definition instanceof AbstractRequestDefinition) {
            throw new CannotApplyRemedy('The halls of this exam are chosen by the scheduler, so add hall seats by hand.');
        }

        foreach ($step['result']['hall_ids'] ?? [] as $hallId) {
            $request = $definition->addHall($request, $issue, $hallId);
        }

        return $request;
    }
}
