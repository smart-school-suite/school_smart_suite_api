<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Course;

use App\Constant\Violation\ExamTimetable\Course\CourseAlreadyScheduled;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Core\State;

/** A course is examined once: a second request for it cannot be honoured. */
class CourseAlreadyScheduledValidator extends BaseValidator
{
    public function check(array $params, State $state): array
    {
        $courseId = $params['course_id'] ?? null;
        if (!$courseId || !$state->isPlaced($courseId)) {
            return [];
        }

        $session = $state->placedCourses[$courseId];

        return [[
            'key' => CourseAlreadyScheduled::KEY,
            'course_id' => $courseId,
            'date' => $session['date'],
            'start_time' => $session['start_time'],
            'end_time' => $session['end_time'],
            'source' => $session['source'],
            'conflict' => $this->conflict($params),
        ]];
    }
}
