<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class ChangeDTO
{
    /**
     * Create a new class instance.
     */

    public function __construct(
    public string $field, // e.g., 'teacher_id', 'hall_id', 'time'
    public string $type, // e.g., 'replace', 'shift'
    public string $reason,
    public BlockerDTO $blocker // e.g., 'teacher_busy', 'hall_busy', 'outside_operational_hours'
    )
    {

    }
}
