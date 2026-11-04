<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentViolation;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot as CourseRequestedSlotViolation;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;

/**
 * Finds requested sessions already pinned in the grid that the proposed session clashes with:
 * the same students cannot sit two exams at once, and a course only has one exam.
 */
class ScheduledSessionValidator implements ValidatorInterface
{
    use BuildsConflict;

    private const KEYS = [
        RequestedAssignment::KEY => RequestedAssignmentViolation::KEY,
        CourseTimeRequest::KEY => CourseRequestedSlotViolation::KEY,
    ];

    public function check(State $state, array $params): array
    {
        $gap = $state->context->sessionGap();
        $blockers = [];

        foreach ($state->sessions() as $session) {
            if (!isset(self::KEYS[$session->source])) {
                continue;
            }

            $sameCourse = ($params['course_id'] ?? null) && $session->courseId === $params['course_id'];
            $clash = $session->date === $params['date'] && $session->overlaps($params['start'] - $gap, $params['end'] + $gap);

            if ($sameCourse || $clash) {
                $blockers[] = $this->blocker($session, $params, $sameCourse ? 'same_course' : 'same_time');
            }
        }

        return $blockers;
    }

    private function blocker(ExamSessionDTO $session, array $params, string $reason): array
    {
        return [
            'key' => self::KEYS[$session->source],
            'reason' => $reason,
            'course_id' => $session->courseId,
            'date' => $session->date,
            'start_time' => $session->startTime(),
            'end_time' => $session->endTime(),
            'hall_id' => $session->halls[0]['hall_id'] ?? null,
            'conflict' => $this->conflict($params),
        ];
    }
}
