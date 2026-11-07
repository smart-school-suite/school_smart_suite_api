<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use Carbon\Carbon;

final class Text
{
    public static function date(?string $date): string
    {
        if (!$date) {
            return 'that date';
        }
        try {
            return Carbon::parse($date)->format('l j F');
        } catch (\Throwable) {
            return $date;
        }
    }

    public static function time(?string $start, ?string $end): string
    {
        return $start && $end ? "{$start} – {$end}" : 'that time';
    }

    public static function when(Subject $subject): string
    {
        return self::date($subject->date()) . ' ' . self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function period(Subject $subject): string
    {
        return self::time($subject->get('start_time'), $subject->get('end_time'));
    }

    public static function course(Subject $subject, ?SolutionContext $context, string $fallback = 'the course'): string
    {
        $id = $subject->get('course_id') ?? $subject->conflict('course_id');
        return $id ? ($context?->courseName($id) ?? $fallback) : $fallback;
    }

    public static function hall(Subject $subject, ?SolutionContext $context, string $fallback = 'the hall'): string
    {
        $id = $subject->get('hall_id') ?? $subject->conflict('hall_id');
        return $id ? ($context?->hallName($id) ?? $fallback) : $fallback;
    }

    public static function invigilator(Subject $subject, ?SolutionContext $context, string $fallback = 'the invigilator'): string
    {
        $id = $subject->get('invigilator_id') ?? $subject->conflict('invigilator_id');
        return $id ? ($context?->invigilatorName($id) ?? $fallback) : $fallback;
    }

    /** "A, B and C" */
    public static function list(array $items): string
    {
        $items = array_values(array_filter($items));
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }
        $last = array_pop($items);
        return implode(', ', $items) . ' and ' . $last;
    }
}
