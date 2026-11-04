<?php

namespace App\Schedular\ExamTimetable\Grid;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * Builds one grid entry per operational exam date and fixes the joint course sessions in it.
 *
 * Joint course sessions are shared with other classes, so their date and time cannot move. When one cannot
 * be held (date outside the exam, outside opening hours, clashing with another joint course, no hall
 * seats or no invigilator) it is a hard violation.
 */
class DayBuilder
{
    public function __construct(
        private OperationalPeriodValidator $operational,
        private HallAvailability $halls,
        private InvigilatorAvailability $invigilators,
    ) {}

    public function build(State $state): void
    {
        foreach ($state->context->dates() as $date) {
            [$start, $end] = $state->context->window($date);
            $state->dateGrid[$date] = new ExamDateDTO($date, $start, $end);
        }

        foreach ($state->context->jointCourses() as $joint) {
            $this->placeJoint($state, $joint);
        }
    }

    private function placeJoint(State $state, array $joint): void
    {
        $params = [
            'date' => $joint['date'],
            'start' => $joint['start'],
            'end' => $joint['end'],
            'course_id' => $joint['course_id'] ?? null,
            'slot_type' => RequiredJointCourseSession::KEY,
        ];

        $blockers = $this->operational->check($state, $params);

        if (empty($blockers)) {
            foreach ($state->sessionsOn($joint['date']) as $other) {
                if ($other->overlaps($joint['start'], $joint['end'])) {
                    $blockers[] = [
                        'key' => RequiredJointCourse::KEY,
                        'course_id' => $other->courseId,
                        'date' => $other->date,
                        'start_time' => $other->startTime(),
                        'end_time' => $other->endTime(),
                        'conflict' => $this->conflict($joint),
                    ];
                }
            }
        }

        $halls = null;

        if (empty($blockers)) {
            $candidates = $state->context->candidateCount($joint['course_id'] ?? null);
            $halls = $this->presetHalls($state, $joint)
                ?? $this->halls->allocate($state, $joint['date'], $joint['start'], $joint['end'], $candidates);

            if ($halls === null) {
                $blockers[] = [
                    'key' => HallUnavailable::KEY,
                    'date' => $joint['date'],
                    'start_time' => Time::toTime($joint['start']),
                    'end_time' => Time::toTime($joint['end']),
                    'seats_needed' => $candidates,
                    'seats_available' => $this->halls->seatsAvailable($state, $joint['date'], $joint['start'], $joint['end']),
                    'conflict' => $this->conflict($joint),
                ];
            } else {
                $staffed = $this->invigilators->staff($state, $halls, $joint['course_id'] ?? null, $joint['date'], $joint['start'], $joint['end']);

                if ($staffed === null) {
                    $blockers[] = [
                        'key' => InvigilatorUnavailable::KEY,
                        'date' => $joint['date'],
                        'start_time' => Time::toTime($joint['start']),
                        'end_time' => Time::toTime($joint['end']),
                        'invigilators_needed' => $this->invigilators->needed($halls),
                        'invigilators_available' => count($this->invigilators->available($state, $joint['course_id'] ?? null, $joint['date'], $joint['start'], $joint['end'])),
                        'conflict' => $this->conflict($joint),
                    ];
                }

                $halls = $staffed;
            }
        }

        if (!empty($blockers)) {
            $state->addViolation('hard', [
                'constraint_failed' => [
                    'key' => RequiredJointCourseSession::KEY,
                    'course_id' => $joint['course_id'] ?? null,
                    'date' => $joint['date'],
                    'start_time' => Time::toTime($joint['start']),
                    'end_time' => Time::toTime($joint['end']),
                ],
                'blockers' => $blockers,
            ]);

            return;
        }

        $state->place(new ExamSessionDTO(
            type: ExamSessionDTO::TYPE_JOINT,
            courseId: $joint['course_id'],
            date: $joint['date'],
            start: $joint['start'],
            end: $joint['end'],
            candidateCount: $state->context->candidateCount($joint['course_id']),
            halls: $halls,
            source: RequiredJointCourseSession::KEY,
        ));
    }

    /** Halls already booked for this joint session by the joint course setup. */
    private function presetHalls(State $state, array $joint): ?array
    {
        $preset = collect($joint['halls'] ?? [])->filter(fn($hall) => !empty($hall['hall_id']));

        if ($preset->isEmpty()) {
            return null;
        }

        return $preset->map(fn($hall) => [
            'hall_id' => $hall['hall_id'],
            'hall_name' => $hall['hall_name'] ?? $state->context->hallName($hall['hall_id']),
            'capacity' => (int) ($hall['capacity'] ?? $state->context->hall($hall['hall_id'])['capacity'] ?? 0),
            'candidate_count' => (int) ($hall['candidate_count'] ?? 0),
            'shared' => false,
            'invigilators' => array_values($hall['invigilators'] ?? []),
        ])->values()->all();
    }

    private function conflict(array $joint): array
    {
        return array_filter([
            'slot_type' => RequiredJointCourseSession::KEY,
            'course_id' => $joint['course_id'] ?? null,
            'date' => $joint['date'],
            'start_time' => Time::toTime($joint['start']),
            'end_time' => Time::toTime($joint['end']),
        ]);
    }
}
