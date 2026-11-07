<?php

namespace App\Exceptions\ExamTimetable;

use App\Exceptions\AppException;

class ExamTimetableActivationConflict extends AppException
{
    public function __construct(private array $conflicts)
    {
        $count = count($conflicts);

        parent::__construct(
            "Exam timetable activation blocked by {$count} conflict(s).",
            422,
            'Exam Timetable Has Conflicts',
            "This exam timetable can't be activated because {$count} exam session(s) clash with exam timetables already in use. Fix the clashes listed below, then try again.",
            null
        );
    }

    public function conflicts(): array
    {
        return $this->conflicts;
    }
}
