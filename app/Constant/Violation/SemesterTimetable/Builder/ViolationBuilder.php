<?php


namespace App\Constant\Violation\SemesterTimetable\Builder;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Constant\Violation\SemesterTimetable\Course\CourseMaxDailyFrequency;
use App\Constant\Violation\SemesterTimetable\Course\RequestedJointCourse;
use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\MaxDailyFreePeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\MaxDailyPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherCourse;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherInsufficiency;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherMaxDailyHours;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherMaxWeeklyHours;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;

final class ViolationBuilder
{
    public static function all(): array
    {
        return [
            TeacherBusy::toArray(),
            TeacherInsufficiency::toArray(),
            TeacherCourse::toArray(),
            TeacherMaxDailyHours::toArray(),
            TeacherMaxWeeklyHours::toArray(),
            TeacherRequestedTimeSlot::toArray(),
            TeacherUnavailable::toArray(),
            HallBusy::toArray(),
            HallRequestedTimeSlot::toArray(),
            CourseMaxDailyFrequency::toArray(),
            RequestedJointCourse::toArray(),
            CourseRequestedSlot::toArray(),
            BreakPeriod::toArray(),
            MaxDailyFreePeriod::toArray(),
            MaxDailyPeriod::toArray(),
            OperationalPeriod::toArray(),
            PeriodDuration::toArray(),
            RequestedFreePeriod::toArray(),
            RequestedAssigment::toArray(),
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
