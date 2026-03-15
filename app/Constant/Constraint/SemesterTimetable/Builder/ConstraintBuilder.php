<?php

namespace App\Constant\Constraint\SemesterTimetable\Builder;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow;
use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherWeeklyHours;

class ConstraintBuilder
{
    public static function all(): array
    {
        return [
            RequestedAssignment::toArray(),
            CourseDailyFrequency::toArray(),
            CourseRequestedSlot::toArray(),
            RequiredJointCourse::toArray(),
            HallRequestedTimeWindow::toArray(),
            BreakPeriod::toArray(),
            OperationalPeriod::toArray(),
            PeriodDuration::toArray(),
            RequestedFreePeriod::toArray(),
            ScheduleDailyPeriod::toArray(),
            ScheduleDailyFreePeriod::toArray(),
            TeacherDailyHours::toArray(),
            TeacherRequestedTimeSlot::toArray(),
            TeacherWeeklyHours::toArray()
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

    public static function constraintInterpreterMap(): array
    {
        $map = [];
        foreach (self::all() as $violation) {
            if (isset($violation['interpreter_handler'])) {
                $map[$violation['key']] = $violation['interpreter_handler'];
            }
        }
        return $map;
    }

    public static function constraintSuggestionMap(): array {
         $map = [];
         foreach(self::all()  as $suggestion) {
             if(isset($suggestion['suggestion_handler'])){
                 $map[$suggestion['key']] = $suggestion['suggestion_handler'];
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
