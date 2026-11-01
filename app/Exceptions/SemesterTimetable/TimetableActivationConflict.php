<?php

namespace App\Exceptions\SemesterTimetable;

use App\Exceptions\AppException;

class TimetableActivationConflict extends AppException
{
    public function __construct(private array $conflicts)
    {
        $count = count($conflicts);

        parent::__construct(
            "Timetable activation blocked by {$count} conflict(s).",
            422,
            'Timetable Has Conflicts',
            "This timetable can't be activated because {$count} lesson(s) clash with timetables already in use. Fix the clashes listed below, then try again.",
            null
        );
    }

    public function conflicts(): array
    {
        return $this->conflicts;
    }
}
