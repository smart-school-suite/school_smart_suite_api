<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as InvigilatorRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorTeachesCourse;
use App\Constant\Violation\ExamTimetable\Schedule\NoExamSession;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * "Let this invigilator invigilate the exam at this date and time."
 *
 * Placement already tries requested invigilators first. If the invigilator
 * was still left out, they are added to the exam at that time when they are
 * free and do not teach the course. A teacher is never put on their own
 * course's exam, whatever is requested.
 */
class InvigilatorRequestedSlot implements ConstraintHandler
{
    public static function supports(): string
    {
        return InvigilatorRequestedSlotConstraint::KEY;
    }

    public static function stage(): string
    {
        return self::AFTER_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();

        foreach ($context->invigilatorRequestedSlots() as $request) {
            if (!$context->invigilator($request['invigilator_id'] ?? '')) {
                continue;
            }

            $blockers = $this->resolve($state, $context, $request);

            if ($blockers) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key' => self::supports(),
                        'invigilator_id' => $request['invigilator_id'],
                        'date' => $request['date'],
                        'start_time' => $request['start_time'],
                        'end_time' => $request['end_time'],
                    ],
                    'blockers' => array_values($blockers),
                ];
            }
        }
    }

    private function resolve(State $state, RequestContext $context, array $request): array
    {
        $params = [...$request, 'slot_type' => self::supports()];

        if ($blockers = app(OperationalPeriodValidator::class)->check($params, $state)) {
            return $blockers;
        }

        $invigilatorId = $request['invigilator_id'];
        $sessions = $state->sessionsOverlapping($request['date'], Time::toMinutes($request['start_time']), Time::toMinutes($request['end_time']));

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
            foreach ($session['halls'] as $hall) {
                if (in_array($invigilatorId, $hall['invigilators'], true)) {
                    return [];
                }
            }
        }

        $blockers = [];

        foreach ($sessions as $session) {
            if ($context->teaches($invigilatorId, $session['course_id'])) {
                $blockers[] = [
                    'key' => InvigilatorTeachesCourse::KEY,
                    'invigilator_id' => $invigilatorId,
                    'course_id' => $session['course_id'],
                    'conflict' => array_filter($params),
                ];
                continue;
            }

            $start = Time::toMinutes($session['start_time']);
            $end = Time::toMinutes($session['end_time']);

            if ($busy = $state->index->invigilatorConflict($invigilatorId, $request['date'], $start, $end)) {
                $blockers[] = [
                    'key' => InvigilatorBusy::KEY,
                    'invigilator_id' => $invigilatorId,
                    ...$busy,
                    'conflict' => array_filter($params),
                ];
                continue;
            }

            // Free and not teaching the course: add them to the hall with the most candidates.
            $hall = collect($session['halls'])->sortByDesc('candidate_count')->first();
            $state->addInvigilator($request['date'], $session['course_id'], $hall['hall_id'], $invigilatorId);
            $state->index->commitInvigilator($invigilatorId, $request['date'], $start, $end, self::supports(), $session['course_id']);

            return [];
        }

        return $blockers;
    }
}
