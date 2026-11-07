<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as HallRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Schedule\NoExamSession;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * "Use this hall for the exam at this date and time."
 *
 * Placement already tries requested halls first, so this checks the result:
 * the request is met when an exam overlapping the window uses the hall.
 */
class HallRequestedSlot implements ConstraintHandler
{
    public static function supports(): string
    {
        return HallRequestedSlotConstraint::KEY;
    }

    public static function stage(): string
    {
        return self::AFTER_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();

        foreach ($context->hallRequestedSlots() as $request) {
            if (!$context->hall($request['hall_id'] ?? '')) {
                continue;
            }

            $blockers = $this->blockers($state, $context, $request);

            if ($blockers) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key' => self::supports(),
                        'hall_id' => $request['hall_id'],
                        'date' => $request['date'],
                        'start_time' => $request['start_time'],
                        'end_time' => $request['end_time'],
                    ],
                    'blockers' => array_values($blockers),
                ];
            }
        }
    }

    private function blockers(State $state, RequestContext $context, array $request): array
    {
        $params = [...$request, 'slot_type' => self::supports()];

        if ($blockers = app(OperationalPeriodValidator::class)->check($params, $state)) {
            return $blockers;
        }

        $start = Time::toMinutes($request['start_time']);
        $end = Time::toMinutes($request['end_time']);
        $sessions = $state->sessionsOverlapping($request['date'], $start, $end);

        if (empty($sessions)) {
            return [[
                'key' => NoExamSession::KEY,
                'date' => $request['date'],
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
                'conflict' => array_filter($params),
            ]];
        }

        foreach ($sessions as $session) {
            if (in_array($request['hall_id'], array_column($session['halls'], 'hall_id'), true)) {
                return [];
            }
        }

        // The hall was tried first, so it was taken by something else.
        $session = $sessions[0];
        $seats = $state->index->hallSeatsFree(
            $request['hall_id'],
            $context->hallCapacity($request['hall_id']),
            $request['date'],
            Time::toMinutes($session['start_time']),
            Time::toMinutes($session['end_time'])
        );

        return [[
            'key' => HallBusy::KEY,
            'hall_id' => $request['hall_id'],
            ...($seats['conflict'] ?? ['date' => $request['date'], 'start_time' => $session['start_time'], 'end_time' => $session['end_time']]),
            'conflict' => array_filter($params),
        ]];
    }
}
