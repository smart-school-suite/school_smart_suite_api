<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Support;

use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\Cost;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

/**
 * A hall or invigilator request is only honoured when an exam runs during it, so it is moved onto the time of
 * an exam in the generated timetable where the hall or invigilator is free. Nearest exams first.
 */
trait MovesToExamSessions
{
    protected function movesToSessions(Subject $target, SolutionContext $context, callable $accepts): array
    {
        $window = $this->window($target);
        $date = $target->date();
        $origin = strtotime((string) $date) ?: 0;

        $moves = $context->sessionWindows()
            ->filter(fn(array $session) => $accepts($session['date'], $session['start'], $session['end']))
            ->reject(fn(array $session) => $window && $session['date'] === $date && $session['start'] === $window[0] && $session['end'] === $window[1])
            ->map(fn(array $session) => [
                ...$session,
                'shift' => $window ? abs($session['start'] - $window[0]) : 0,
                'distance' => abs((strtotime($session['date']) ?: 0) - $origin),
            ])
            ->sortBy([['distance', 'asc'], ['shift', 'asc']])
            ->take(SolutionContext::MAX_ALTERNATIVES)
            ->map(fn(array $session) => $this->moveTo($target, $context, $session, Cost::MOVE_REQUEST, $this->occupiesWith($target)))
            ->values()
            ->all();

        return [...$moves, $this->remove($target, Cost::REMOVE_REQUEST, context: $context)];
    }
}
