<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Support\Time;
use Carbon\Carbon;

final class Text
{
    public static function date(?string $date): string
    {
        if (!$date || $date === Issue::UNSCHEDULED) {
            return $date === Issue::UNSCHEDULED ? 'Not scheduled' : 'that date';
        }

        try {
            return Carbon::parse($date)->format('D j M Y');
        } catch (\Throwable) {
            return $date;
        }
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
        return self::date($subject->date()) . ' ' . self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function period(Subject $subject): string
    {
        return self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function minutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return trim(($hours ? "{$hours}h " : '') . ($rest ? "{$rest}min" : '')) ?: '0min';
    }

    public static function course(Subject $subject, ?SolutionContext $context, string $fallback = 'The course'): string
    {
        $id = $subject->get('course_id') ?? ($subject->get('conflict')['course_id'] ?? null);

        return $id ? ($context?->courseName($id) ?? $fallback) : $fallback;
    }

    public static function hall(Subject $subject, ?SolutionContext $context, string $fallback = 'The hall'): string
    {
        $id = $subject->get('hall_id') ?? ($subject->get('conflict')['hall_id'] ?? null);

        return $id ? ($context?->hallName($id) ?? $fallback) : $fallback;
    }

    public static function invigilator(Subject $subject, ?SolutionContext $context, string $fallback = 'The invigilator'): string
    {
        $id = $subject->get('invigilator_id') ?? ($subject->get('conflict')['invigilator_id'] ?? null);

        return $id ? ($context?->invigilatorName($id) ?? $fallback) : $fallback;
    }
}
