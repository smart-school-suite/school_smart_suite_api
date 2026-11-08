<?php

namespace App\Exceptions\ExamJointCourse;

use App\Exceptions\AppException;

class ExamJointCourseSlotConflict extends AppException
{
    public function __construct(private array $conflicts)
    {
        $count = count($conflicts);

        parent::__construct(
            "Joint exam time blocked by {$count} problem(s).",
            422,
            'Joint Exam Time Not Possible',
            "This time can't be used for the joint exam because of the problem(s) listed below. Pick another time.",
            null
        );
    }

    public function conflicts(): array
    {
        return $this->conflicts;
    }
}
