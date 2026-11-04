<?php

namespace App\Constant\Constraint\ExamTimetable\Course;

class CourseExamSession
{
    public const KEY = "course_exam_session";
    public const TITLE = "Course Exam Session";
    public const TYPE = "hard";
    public const CATEGORY = "schedule_constraint";
    public const DESCRIPTION = "Every course in the exam must get one exam session inside the exam dates, with enough hall seats and at least one invigilator for each hall it uses.";

    public static function toArray(): array
    {
        return [
            'key' => self::KEY,
            'title' => self::TITLE,
            'type' => self::TYPE,
            'description' => self::DESCRIPTION,
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
