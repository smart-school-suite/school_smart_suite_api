<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

class ChangeDTO
{
    /**
     * Create a new class instance.
     */
    public string $field; // e.g., 'teacher_id', 'hall_id', 'time'
    public string $type; // e.g., 'replace', 'shift'
    public string $reason; // e.g., 'teacher_busy', 'hall_busy', 'outside_operational_hours'
    public function __construct(
        string $field,
        string $type,
        string $reason
    )
    {

    }
}
