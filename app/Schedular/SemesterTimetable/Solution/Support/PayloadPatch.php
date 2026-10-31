<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

final class PayloadPatch
{
    public static function dayException(string $constraintKey, string $day, array $values): \Closure
    {
        return function (array $payload) use ($constraintKey, $day, $values): array {
            $exceptions = collect($payload['hard_constraints'][$constraintKey]['day_exceptions'] ?? [])
                ->reject(fn($exception) => strtolower($exception['day'] ?? '') === strtolower($day))
                ->values()
                ->all();

            $exceptions[] = ['day' => $day, ...$values];
            $payload['hard_constraints'][$constraintKey]['day_exceptions'] = $exceptions;

            return $payload;
        };
    }

    public static function noBreakOn(string $day): \Closure
    {
        return function (array $payload) use ($day): array {
            $days = $payload['hard_constraints']['break_period']['no_break_exceptions'] ?? [];
            $days = is_array($days) ? $days : [];

            if (!in_array($day, array_map('strtolower', $days), true)) {
                $days[] = $day;
            }

            $payload['hard_constraints']['break_period']['no_break_exceptions'] = $days;

            return $payload;
        };
    }
}
