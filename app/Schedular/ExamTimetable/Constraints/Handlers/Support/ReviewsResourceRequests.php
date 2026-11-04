<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Support;

use App\Constant\Violation\ExamTimetable\Schedule\NoExamInWindow;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * Invigilator and hall requests ("use X between these times") are honoured as preferences during placement.
 * After placement each request is checked: it is honoured when an exam overlapping the window uses X.
 */
abstract class ReviewsResourceRequests implements ConstraintHandler
{
    abstract protected function field(): string;

    abstract protected function entries(State $state): array;

    /** Blockers explaining why X could not be used in the window. */
    abstract protected function resourceBlockers(State $state, array $params): array;

    public function phase(): string
    {
        return ConstraintHandler::AFTER_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $field = $this->field();

        foreach ($this->entries($state) as $entry) {
            $id = $entry[$field] ?? null;
            $sessions = collect($state->sessionsOn($entry['date']))->filter(fn($session) => $session->overlaps($entry['start'], $entry['end']));
            $used = $sessions->contains(fn($session) => in_array($id, $field === 'hall_id' ? $session->hallIds() : $session->invigilatorIds(), true));

            if ($used) {
                continue;
            }

            $params = [
                'date' => $entry['date'],
                'start' => $entry['start'],
                'end' => $entry['end'],
                $field => $id,
                'slot_type' => static::supports(),
            ];

            $blockers = app(OperationalPeriodValidator::class)->check($state, $params);

            if (empty($blockers)) {
                $blockers = $this->resourceBlockers($state, $params);
            }

            if (empty($blockers) && $sessions->isEmpty()) {
                $blockers[] = [
                    'key' => NoExamInWindow::KEY,
                    'date' => $entry['date'],
                    'start_time' => Time::toTime($entry['start']),
                    'end_time' => Time::toTime($entry['end']),
                    'conflict' => array_filter([...$params, 'start' => null, 'end' => null, 'start_time' => Time::toTime($entry['start']), 'end_time' => Time::toTime($entry['end'])]),
                ];
            }

            $state->addViolation('soft', [
                'constraint_failed' => [
                    'key' => static::supports(),
                    $field => $id,
                    'date' => $entry['date'],
                    'start_time' => Time::toTime($entry['start']),
                    'end_time' => Time::toTime($entry['end']),
                ],
                'blockers' => $blockers,
            ]);
        }
    }
}
