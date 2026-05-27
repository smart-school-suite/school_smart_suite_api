<?php

namespace App\Constant\Violation\ExamTimetable\Builder;

use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;

class ViolationBuilder
{
    public static function all(): array
    {
        return [
            RequestedAssignment::toArray(),
            CourseRequestedSlot::toArray(),
            RequiredJointCourse::toArray(),
            HallBusy::toArray(),
            HallRequestedSlot::toArray(),
            HallUnavailable::toArray(),
            InvigilatorBusy::toArray(),
            InvigilatorRequestedSlot::toArray(),
            InvigilatorUnavailable::toArray(),
            OperationalPeriod::toArray(),
            SessionDuration::toArray(),
            StudentDailyLoad::toArray()
        ];
    }

    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function titles(): array
    {
        return array_column(self::all(), 'title', 'key');
    }

    public static function title(string $key, string $default = 'Unknown violation'): string
    {
        return self::titles()[$key] ?? $default;
    }

    public static function violationHandlerMap(): array
    {
        $map = [];
        foreach (self::all() as $violation) {
            if (isset($violation['violation_handler'])) {
                $map[$violation['key']] = $violation['violation_handler'];
            }
        }
        return $map;
    }

    public static function violationSuggestionHandlerMap(): array
    {
        $map = [];
        foreach (self::all() as $violation) {
            if (isset($violation['violation_suggestion_handler'])) {
                $map[$violation['key']] = $violation['violation_suggestion_handler'];
            }
        }
        return $map;
    }
    public static function get(string $key): ?array
    {
        foreach (self::all() as $violation) {
            if ($violation['key'] === $key) {
                return $violation;
            }
        }
        return null;
    }

    public static function has(string $key): bool
    {
        return in_array($key, self::keys(), true);
    }

    public static function categories(): array
    {
        $categories = array_column(self::all(), 'category');
        return array_values(array_unique($categories));
    }

    public static function byCategory(): array
    {
        $grouped = [];

        foreach (self::all() as $violation) {
            $cat = $violation['category'] ?? 'other';
            $grouped[$cat][] = $violation;
        }

        return $grouped;
    }

    public static function isOfCategory(string $key, string $category): bool
    {
        $violation = self::get($key);
        return $violation && ($violation['category'] ?? null) === $category;
    }
}
