<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Contracts;

use App\Schedular\ExamTimetable\Core\State;

interface ValidatorInterface
{
    /**
     * @param array $params the request being checked: date, start_time, end_time,
     *                      course_id?, hall_ids?, invigilator_ids?, slot_type
     * @return array[] blockers, empty when nothing is in the way
     */
    public function check(array $params, State $state): array;
}
