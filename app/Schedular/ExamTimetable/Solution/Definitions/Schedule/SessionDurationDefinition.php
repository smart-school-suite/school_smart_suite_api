<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration as Constraint;
use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration as Violation;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\ChangesExamSchedule;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class SessionDurationDefinition extends AbstractDefinition
{
    use ChangesExamSchedule;

    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Exam length';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The ' . Text::course($subject, $context) . ' exam (' . $subject->get('duration') . ' minutes) is longer than any exam day';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The ' . Text::course($obstacle, $context) . ' exam lasts ' . $obstacle->get('allowed_duration')
            . ' minutes, but ' . $obstacle->get('requested_duration') . ' were requested';
    }

    /** Hard failure: the exam is longer than the longest exam day; make it fit. */
    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $courseId = $issue->subject->get('course_id');
        $longest = collect($context->dates())->map(fn(string $date) => ($w = $context->window($date)) ? $w[1] - $w[0] : 0)->max() ?? 0;
        $fits = intdiv((int) $longest, 15) * 15;

        return $courseId && $fits > 0 ? [$this->courseDuration($issue->subject, $context, $courseId, $fits)] : [];
    }

    /** A course request whose length differs from the exam length. */
    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        $issue = collect($issues)->first(fn(Issue $issue) => $issue->window() && $issue->subject->get('course_id'));
        if (!$issue) {
            return [];
        }

        [$start] = $issue->window();
        $expected = (int) $obstacle->get('allowed_duration');
        $requested = (int) $obstacle->get('requested_duration');
        $newEnd = Time::toTime($start + $expected);
        $courseId = $issue->subject->get('course_id');

        return [
            // Fit the request to the exam length (applied to the request itself).
            new Remedy(
                action: Remedy::MOVE,
                target: $issue->subject,
                label: 'Change the requested time to ' . Text::time($issue->subject->get('start_time'), $newEnd) . " to match the {$expected}-minute exam",
                changes: [['field' => 'end_time', 'from' => $issue->subject->get('end_time'), 'to' => $newEnd]],
                result: ['date' => $issue->subject->date(), 'start_time' => $issue->subject->get('start_time'), 'end_time' => $newEnd],
                cost: Cost::MOVE_REQUEST + Cost::shift($expected - $requested),
                occupies: ['date' => $issue->subject->date(), 'start' => $start, 'end' => $start + $expected],
            ),
            $this->courseDuration($obstacle, $context, $courseId, $requested),
        ];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        return $this->applySchedule($request, $step);
    }
}
