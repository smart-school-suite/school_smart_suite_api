<?php

namespace App\Schedular\ExamTimetable\DTO;

/**
 * Names used by a session in State::$dateGrid[$date]->allocations.
 *
 * A session is an array:
 *  [
 *    'type'            => regular | joint,
 *    'source'          => placement | course_requested_slot | requested_assignment | required_joint_course_periods,
 *    'course_id'       => string,
 *    'start_time'      => 'HH:MM',
 *    'end_time'        => 'HH:MM',
 *    'candidate_count' => int,
 *    'halls'           => [['hall_id', 'hall_name', 'capacity', 'candidate_count', 'shared', 'invigilators' => [ids]]],
 *  ]
 */
class AllocationDTO
{
    public const TYPE_REGULAR = 'regular';
    public const TYPE_JOINT   = 'joint';

    public const SOURCE_PLACEMENT = 'placement';
}
