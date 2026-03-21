<?php

namespace App\Constant\Violation\SemesterTimetable\Course;

class RequiredJointCourse
{
    public const KEY = "joint_course_period_violation";
    public const TITLE = "Requested Joint Course Period Violation";
    public const HANDLER = \App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse::class;
    public const VIOLATION_HANDLER = \App\Interpreter\SemesterTimetable\Violation\Interpreters\Course\RequiredJointCourseViolation::class;
    public const CATEGORY = "course_violation";
    public const DESCRIPTION = "This violation occurs when courses that are required to be scheduled together (joint courses) are not scheduled in the same time slot. The required joint course constraint ensures that certain courses, which may be part of a combined program or have overlapping content, are scheduled simultaneously to facilitate student attendance and resource allocation. Violating this constraint indicates that the timetable has assigned these joint courses to different time slots, which can lead to scheduling conflicts for students and teachers involved in those courses.";
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'violation_handler' => self::VIOLATION_HANDLER,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION

        ];
    }

    public static function title(): string
    {
        return self::TITLE;
    }

    public static function key(): string
    {
        return self::KEY;
    }
}
