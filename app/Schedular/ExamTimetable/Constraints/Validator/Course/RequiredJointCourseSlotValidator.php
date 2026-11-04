<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

class RequiredJointCourseSlotValidator implements ValidatorInterface
{
    use BuildsConflict;

    public function check(State $state, array $params): array
    {
        $blockers = [];

        foreach ($state->context->jointCourses() as $joint) {
            if ($joint['date'] !== $params['date'] || !Time::overlaps($params['start'] - $state->context->sessionGap(), $params['end'] + $state->context->sessionGap(), $joint['start'], $joint['end'])) {
                continue;
            }

            if (($params['slot_type'] ?? null) === 'joint' && ($params['course_id'] ?? null) === $joint['course_id'] && $params['start'] === $joint['start']) {
                continue;
            }

            $blockers[] = [
                'key' => RequiredJointCourse::KEY,
                'course_id' => $joint['course_id'],
                'date' => $joint['date'],
                'start_time' => Time::toTime($joint['start']),
                'end_time' => Time::toTime($joint['end']),
                'conflict' => $this->conflict($params),
            ];
        }

        return $blockers;
    }
}
