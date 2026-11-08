<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange as StudentDailyLoadRangeConstraint;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;

/**
 * Checks the number of exams per date against the daily range.
 *
 * Placement keeps dates under the maximum and records every date it had to
 * push over it. This adds the dates that have exams but fewer than the minimum.
 */
class StudentDailyLoadRange implements ConstraintHandler
{
    public static function supports(): string
    {
        return StudentDailyLoadRangeConstraint::KEY;
    }

    public static function stage(): string
    {
        return self::AFTER_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();
        if (!$context->hasDailyLoadRange()) {
            return;
        }

        foreach (array_keys($state->dateGrid) as $date) {
            $load = $state->load($date);
            $min = $context->dailyLoadFor($date)['min_sessions'];

            if ($load === 0 || $min === null || $load >= $min) {
                continue;
            }

            $state->violations['soft'][] = [
                'constraint_failed' => [
                    'key' => self::supports(),
                    'date' => $date,
                    'breach' => 'min',
                    'min_sessions' => $min,
                    'sessions' => $load,
                ],
                'blockers' => [[
                    'key' => StudentDailyLoad::KEY,
                    'date' => $date,
                    'breach' => 'min',
                    'min_sessions' => $min,
                    'sessions' => $load,
                    'course_ids' => array_column($state->sessions($date), 'course_id'),
                    'conflict' => ['date' => $date, 'slot_type' => self::supports()],
                ]],
            ];
        }
    }
}
