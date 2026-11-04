<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Support;

use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Validator\Assignment\ScheduledSessionValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\RequiredJointCourseSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Hall\HallValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Invigilator\InvigilatorValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\SessionDurationValidator;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Placement\Preferences;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * Shared work of the handlers that put a course at a requested date and time.
 * The request is checked with every validator; when nothing is in the way the session is pinned in the grid,
 * otherwise a soft violation is recorded and the placement engine schedules the course on its own.
 */
abstract class PinsRequestedSessions implements ConstraintHandler
{
    /** @return array[] the requested entries */
    abstract protected function entries(State $state): array;

    /** Fields of the entry that identify it in the diagnostic. */
    abstract protected function identity(array $entry): array;

    public function phase(): string
    {
        return ConstraintHandler::BEFORE_PLACEMENT;
    }

    public function handle(State $state): void
    {
        foreach ($this->entries($state) as $entry) {
            $this->pin($state, $entry);
        }
    }

    protected function pin(State $state, array $entry): void
    {
        $params = [
            'date' => $entry['date'],
            'start' => $entry['start'],
            'end' => $entry['end'],
            'course_id' => $entry['course_id'] ?? null,
            'hall_id' => $entry['hall_id'] ?? null,
            'invigilator_id' => $entry['invigilator_id'] ?? null,
            'slot_type' => static::supports(),
        ];

        if (!$params['course_id'] || !$state->context->course($params['course_id'])) {
            $this->fail($state, $entry, [], 'course_not_in_exam');
            return;
        }

        $blockers = app(OperationalPeriodValidator::class)->check($state, $params);

        if (empty($blockers)) {
            $blockers = [
                ...app(SessionDurationValidator::class)->check($state, $params),
                ...app(RequiredJointCourseSlotValidator::class)->check($state, $params),
                ...app(ScheduledSessionValidator::class)->check($state, $params),
                ...app(HallValidator::class)->check($state, $params),
                ...app(InvigilatorValidator::class)->check($state, $params),
            ];
        }

        if (!empty($blockers)) {
            $this->fail($state, $entry, $blockers);
            return;
        }

        $preferences = app(Preferences::class);
        $candidates = $state->context->candidateCount($params['course_id']);
        $halls = app(HallAvailability::class)->allocate(
            $state,
            $params['date'],
            $params['start'],
            $params['end'],
            $candidates,
            $preferences->halls($state, $params['date'], $params['start'], $params['end']),
            $params['hall_id'] ? [$params['hall_id']] : null,
        );

        if ($halls === null) {
            $this->fail($state, $entry, [[
                'key' => HallUnavailable::KEY,
                'date' => $params['date'],
                'start_time' => Time::toTime($params['start']),
                'end_time' => Time::toTime($params['end']),
                'seats_needed' => $candidates,
                'seats_available' => app(HallAvailability::class)->seatsAvailable($state, $params['date'], $params['start'], $params['end']),
                'conflict' => $this->conflictOf($params),
            ]]);
            return;
        }

        $staffed = app(InvigilatorAvailability::class)->staff(
            $state,
            $halls,
            $params['course_id'],
            $params['date'],
            $params['start'],
            $params['end'],
            $preferences->invigilators($state, $params['date'], $params['start'], $params['end']),
            array_filter([$params['invigilator_id']]),
        );

        if ($staffed === null) {
            $this->fail($state, $entry, [[
                'key' => InvigilatorUnavailable::KEY,
                'date' => $params['date'],
                'start_time' => Time::toTime($params['start']),
                'end_time' => Time::toTime($params['end']),
                'invigilators_needed' => app(InvigilatorAvailability::class)->needed($halls),
                'invigilators_available' => count(app(InvigilatorAvailability::class)->available($state, $params['course_id'], $params['date'], $params['start'], $params['end'])),
                'conflict' => $this->conflictOf($params),
            ]]);
            return;
        }

        $state->place(new ExamSessionDTO(
            type: ExamSessionDTO::TYPE_REGULAR,
            courseId: $params['course_id'],
            date: $params['date'],
            start: $params['start'],
            end: $params['end'],
            candidateCount: $candidates,
            halls: $staffed,
            source: static::supports(),
        ));
    }

    protected function fail(State $state, array $entry, array $blockers, ?string $reason = null): void
    {
        $state->addViolation('soft', [
            'constraint_failed' => array_filter([
                'key' => static::supports(),
                ...$this->identity($entry),
                'date' => $entry['date'],
                'start_time' => Time::toTime($entry['start']),
                'end_time' => Time::toTime($entry['end']),
                'reason' => $reason,
            ], fn($value) => $value !== null),
            'blockers' => $blockers,
        ]);
    }

    private function conflictOf(array $params): array
    {
        return array_filter([
            'slot_type' => $params['slot_type'],
            'course_id' => $params['course_id'],
            'hall_id' => $params['hall_id'],
            'invigilator_id' => $params['invigilator_id'],
            'date' => $params['date'],
            'start_time' => Time::toTime($params['start']),
            'end_time' => Time::toTime($params['end']),
        ]);
    }
}
