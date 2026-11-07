<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\ChangesExamSchedule;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Not enough free seats at a time. Requests can move; otherwise exams need more time or more seats. */
class HallUnavailableDefinition extends AbstractDefinition
{
    use ChangesExamSchedule;

    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Not enough free seats';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $when = $obstacle->date() ? ' on ' . Text::when($obstacle) : '';
        return 'Only ' . $obstacle->get('seats_free', 0) . ' of the ' . $obstacle->get('seats_needed') . " seats needed are free{$when}";
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $duration = (int) (collect($issues)->map(fn($issue) => $issue->subject->get('duration'))->filter()->first() ?? 0);

        return [
            ...($duration ? $this->moreExamTime($obstacle, $context, $duration) : []),
            $this->review($obstacle, 'Give more halls the exam hall type, or raise hall capacity, so ' . $obstacle->get('seats_needed') . ' candidates can sit at once'),
        ];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        return $this->applySchedule($request, $step);
    }
}
