<?php

namespace App\Constant\Constraint\SemesterTimetable\Soft;

use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintBuilder;
use App\Constant\Constraint\Builders\SemesterTimetable\ConstraintGuide;

class RequestedAssignmentConstraint
{
    public static function make(): ConstraintGuide
    {
        return ConstraintBuilder::make()
            ->name('Requested Assignments')
            ->programName('requested_assignments')
            ->type('Soft')
            ->code('RAS')
            ->description('Specifies user-preferred assignments of courses to particular teachers, halls/rooms, days and/or time slots. These are soft placement requests — the scheduler should try to respect them when possible, but can assign differently if needed due to conflicts or other rules.')
            ->examples([
                [
                    [
                        "course_id"  => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "hall_id"    => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day"        => "monday",
                        "start_time" => "09:00",
                        "end_time"   => "10:30"
                    ]
                ],
                [
                    [
                        "course_id"  => "math101",
                        "teacher_id" => "teacherX",
                        "hall_id"    => "roomA",
                        "day"        => "friday"
                    ],
                    [
                        "course_id"  => "physics202",
                        "teacher_id" => "teacherY",
                        "hall_id"    => "labB",
                        "start_time" => "11:00",
                        "end_time"   => "12:30"
                    ]
                ],
                [
                    [
                        "course_id"  => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "hall_id"    => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day"        => "tuesday"
                    ]
                ],
                [
                    [
                        "course_id"  => "english301",
                        "teacher_id" => "msJohnson",
                        "hall_id"    => "hallC",
                        "start_time" => "08:00",
                        "end_time"   => "09:45"
                    ]
                ],
                [
                    [
                        "course_id"  => "biology405",
                        "teacher_id" => "drLee",
                        "hall_id"    => "labA"
                    ]
                ]
            ])
            ->build();
    }
}
