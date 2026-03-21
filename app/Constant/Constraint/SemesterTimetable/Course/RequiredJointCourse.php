<?php

namespace App\Constant\Constraint\SemesterTimetable\Course;

class RequiredJointCourse
{
    public const KEY = "required_joint_course_periods";
    public const TITLE = "Required Joint Course Periods";
    public const DESCRIPTION = "Ensures that certain courses are scheduled together in the same periods across different department.";
    public const INTERPRETER_HANDLER = \App\Interpreter\SemesterTimetable\Interpreters\Course\RequiredJointCoursePeriodInterpreter::class;
    public const HANDLER = \App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse::class;
    public const TYPE = "hard";
    public const CATEGORY = "course_constraint";
    public const VIOLATION = [
        "break_period_violation",
        "operational_period_violation"
    ];
    public const EXAMPLE = [
        [
            "course_id" => "00d6c93b-4bf9-4634-adc2-293f3c513c18",
            "teacher_id" =>  "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
            "periods" => [
                [
                    "day" => "monday",
                    "start_time" => "10:00",
                    "end_time" => "11:00"
                ],
                [
                    "day" => "wednesday",
                    "start_time" => "14:00",
                    "end_time" => "15:00"
                ]
            ]
        ]
    ];
    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'handler' => self::HANDLER,
            'interpreter_handler' => self::INTERPRETER_HANDLER,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
            'category' => self::CATEGORY
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
