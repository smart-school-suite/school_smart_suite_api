<?php

namespace App\Constant\Timetable\Constraints\Guides\Soft;

use App\Constant\Timetable\Constraints\Guides\ConstraintGuideBuilder;
use App\Constant\Timetable\Constraints\Guides\ConstraintGuide;

class RequestedAssignmentGuide
{
    public static function make(): ConstraintGuide
    {
        return ConstraintGuideBuilder::make('requested_assignments')
            ->intent('Specifies user-requested preferred assignments of a course to a particular teacher, hall, day and/or time slot. These are soft placement requests — the scheduler should try to honor each one when possible, but may assign the course elsewhere if hard constraints (teacher availability, room conflicts, etc.) prevent it.')
            ->whenToUse('Use this constraint ONLY when the user explicitly requests or suggests specific placements like "put math with Mr. X on Monday at 9", "schedule biology in lab room A on Tuesday morning", "I want teacher Y to teach physics at 11:00", "assign this class to hall Z", or any combination of course + teacher + hall + day/time preferences.')
            ->requiredFields(['course_id', 'teacher_id', 'hall_id'])
            ->optionalFields(['day', 'start_time', 'end_time'])
            ->howToUse([
                'The value must ALWAYS be an array of assignment request objects (can be empty, but usually non-empty when used).',
                'Each object represents one requested placement and must contain at least: course_id, teacher_id, hall_id.',
                'Valid combinations of day/start_time/end_time:',

                '1. Full specificity (day + time): { ..., "day": "monday", "start_time": "09:00", "end_time": "10:30" }',
                '   → most precise request',

                '2. Day only: { ..., "day": "tuesday" }',
                '   → prefers the day, any valid time slot on that day',

                '3. Time slot only (no day): { ..., "start_time": "08:00", "end_time": "10:30" }',
                '   → prefers the time window, on any suitable day',

                'Every request object MUST include course_id, teacher_id, and hall_id — these three are always required.',
                'day, start_time, and end_time are all OPTIONAL, but if any time fields are included, both start_time and end_time must be present together.',
                'Never generate a request object missing course_id, teacher_id, or hall_id.',
                'Do NOT add day unless the user explicitly mentions a preferred day.',
                'Do NOT add start_time/end_time unless the user explicitly mentions a preferred time or time window.',
                'Never invent or assume values for day, start_time, end_time, course_id, teacher_id, or hall_id — only use what the user clearly states.',
                'Multiple requests are allowed (and common) when the user gives several specific placement wishes.',
                'This is a SOFT preference — do NOT treat it as a hard constraint; always allow the scheduler to deviate when necessary and explain deviations in diagnostics if used.'
            ])
            ->examples([
                [
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day" => "monday",
                        "start_time" => "09:00",
                        "end_time" => "10:30"
                    ],
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day" => "tuesday",
                        "start_time" => "11:00",
                        "end_time" => "12:30"
                    ]
                ],
                [
                    [
                        "course_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "teacher_id" => "f1e2d3c4-b5a6-7890-1234-56789abcdef0",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day" => "monday"
                    ],
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "day" => "tuesday",
                        "start_time" => "11:00",
                        "end_time" => "12:30"
                    ]
                ],
                [
                    [
                        "course_id" => "s1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "teacher_id" => "a1b2c3d4-e5f6-g7h8-i9j0-k1l2m3n4o5p6",
                        "hall_id" => "h1a2b3c4-d5e6-f7g8-h9i0-j1k2l3m4n5o6",
                        "start_time" => "08:00",
                        "end_time" => "10:30"
                    ]
                ],
                [
                    [
                        "course_id" => "math101",
                        "teacher_id" => "teacherX",
                        "hall_id" => "roomA",
                        "day" => "friday"
                    ]
                ]
            ])
            ->build();
    }
}
