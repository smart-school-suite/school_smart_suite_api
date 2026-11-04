<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Schedular\ExamTimetable\Constraints\Handlers\Support\PinsRequestedSessions;
use App\Schedular\ExamTimetable\Core\State;

/** A course at a requested date and time; halls and invigilators are chosen by the engine. */
class CourseRequestedSlot extends PinsRequestedSessions
{
    public static function supports(): string
    {
        return CourseTimeRequest::KEY;
    }

    protected function entries(State $state): array
    {
        return $state->context->courseRequestedSlots();
    }

    protected function identity(array $entry): array
    {
        return ['course_id' => $entry['course_id'] ?? null];
    }
}
