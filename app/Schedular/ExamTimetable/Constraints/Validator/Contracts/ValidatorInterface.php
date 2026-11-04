<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Contracts;

use App\Schedular\ExamTimetable\Core\State;

interface ValidatorInterface
{
    /**
     * Checks one proposed exam session.
     *
     * $params: date, start, end (minutes), course_id?, hall_id?, invigilator_id?, slot_type (constraint key)
     *
     * @return array[] blockers: ['key' => violation key, ...details, 'conflict' => the proposed session]
     */
    public function check(State $state, array $params): array;
}
