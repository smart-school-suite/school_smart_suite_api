<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

use App\Schedular\SemesterTimetable\Solution\DTO\Subject;

final class Text
{
    public static function day(?string $day): string
    {
        return $day ? ucfirst(strtolower($day)) : 'that day';
    }

    public static function time(?string $start, ?string $end): string
    {
        return $start && $end ? self::clock($start) . ' – ' . self::clock($end) : 'that time';
    }

    public static function clock(string $time): string
    {
        $minutes = Time::toMinutes($time);

        return $minutes === null ? $time : Time::toTime($minutes);
    }

    public static function when(Subject $subject): string
    {
        return self::day($subject->day()) . ' ' . self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function period(Subject $subject): string
    {
        return self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function teacher(Subject $subject, ?SolutionContext $context, string $fallback = 'The teacher'): string
    {
        $id = $subject->get('teacher_id') ?? ($subject->get('conflict')['teacher_id'] ?? null);

        return $id ? ($context?->teacherName($id) ?? $fallback) : $fallback;
    }

    public static function hall(Subject $subject, ?SolutionContext $context, string $fallback = 'The hall'): string
    {
        $id = $subject->get('hall_id') ?? ($subject->get('conflict')['hall_id'] ?? null);

        return $id ? ($context?->hallName($id) ?? $fallback) : $fallback;
    }

    public static function course(Subject $subject, ?SolutionContext $context, string $fallback = 'The course'): string
    {
        $id = $subject->get('course_id') ?? ($subject->get('conflict')['course_id'] ?? null);

        return $id ? ($context?->courseName($id) ?? $fallback) : $fallback;
    }
}
