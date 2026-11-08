<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Concerns;

use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * Hall and invigilator requests attach to the exam taking place at their
 * time. Moving one means picking another exam of the timetable it can join.
 */
trait AttachesToSessions
{
    /** True when the request's hall or invigilator can join this session. */
    abstract protected function canJoin(Subject $target, array $session, SolutionContext $context): bool;

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $target = $issue->subject;
        $date = $target->date();
        $start = $target->get('start_time') ? Time::toMinutes($target->get('start_time')) : 0;

        $sessions = collect($context->sessions())
            ->filter(fn(array $session) => $this->canJoin($target, $session, $context))
            ->sortBy(fn(array $session) => [
                $session['date'] === $date ? 0 : 1,
                abs(strtotime($session['date']) - strtotime($date ?? $session['date'])),
                abs(Time::toMinutes($session['start_time']) - $start),
            ])
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn(array $session) => new Remedy(
                action: Remedy::MOVE,
                target: $target,
                label: 'Move ' . lcfirst($this->describe($target, $context)) . ' to the '
                    . ($context->courseName($session['course_id']) ?? 'other') . ' exam on '
                    . Text::date($session['date']) . ' ' . Text::time($session['start_time'], $session['end_time']),
                changes: array_values(array_filter([
                    $session['date'] !== $date ? ['field' => 'date', 'from' => $date, 'to' => $session['date']] : null,
                    ['field' => 'start_time', 'from' => $target->get('start_time'), 'to' => $session['start_time']],
                    ['field' => 'end_time', 'from' => $target->get('end_time'), 'to' => $session['end_time']],
                ])),
                result: ['date' => $session['date'], 'start_time' => $session['start_time'], 'end_time' => $session['end_time']],
                cost: Cost::MOVE_REQUEST + ($session['date'] !== $date ? Cost::CHANGE_DATE : 0)
                    + Cost::shift(abs(Time::toMinutes($session['start_time']) - $start)),
            ))
            ->values()
            ->all();

        return [...$sessions, $this->remove($target, Cost::REMOVE_REQUEST, context: $context)];
    }
}
