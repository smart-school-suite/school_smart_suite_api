<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange as StudentDailyLoadRangeConstraint;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Core\State;

/**
 * Checks the number of exams per date against the min/max range after placement.
 * The placement engine already avoids going over the maximum; it only does so when a course would otherwise
 * not be scheduled at all.
 */
class StudentDailyLoadRange implements ConstraintHandler
{
    public static function supports(): string
    {
        return StudentDailyLoadRangeConstraint::KEY;
    }

    public function phase(): string
    {
        return ConstraintHandler::AFTER_PLACEMENT;
    }

    public function handle(State $state): void
    {
        foreach (array_keys($state->dateGrid) as $date) {
            [$min, $max] = $state->context->dailyLoad($date);
            $count = $state->load($date);

            $breach = match (true) {
                $max !== null && $count > $max => 'max',
                $min !== null && $count > 0 && $count < $min => 'min',
                default => null,
            };

            if (!$breach) {
                continue;
            }

            $state->addViolation('soft', [
                'constraint_failed' => array_filter([
                    'key' => self::supports(),
                    'date' => $date,
                    'breach' => $breach,
                    'session_count' => $count,
                    'min_sessions' => $min,
                    'max_sessions' => $max,
                ], fn($value) => $value !== null),
                'blockers' => [array_filter([
                    'key' => StudentDailyLoad::KEY,
                    'date' => $date,
                    'breach' => $breach,
                    'session_count' => $count,
                    'min_sessions' => $min,
                    'max_sessions' => $max,
                    'conflict' => ['slot_type' => self::supports(), 'date' => $date],
                ], fn($value) => $value !== null)],
            ]);
        }
    }
}
