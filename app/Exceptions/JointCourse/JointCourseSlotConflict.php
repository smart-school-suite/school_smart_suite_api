<?php

namespace App\Exceptions\JointCourse;

use App\Exceptions\AppException;

class JointCourseSlotConflict extends AppException
{
    public function __construct(private array $conflicts)
    {
        $count = count($conflicts);

        parent::__construct(
            "Joint course slots blocked by {$count} conflict(s).",
            422,
            'Joint Course Times Clash',
            "These times can't be saved because {$count} of them clash with other lessons. Change the times listed below, then save again.",
            null
        );
    }

    public function conflicts(): array
    {
        return $this->conflicts;
    }
}
