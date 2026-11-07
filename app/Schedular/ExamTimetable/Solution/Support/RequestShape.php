<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

/**
 * Solutions change the generation request (what the user sent). The
 * scheduler input holds the same constraints under hard_constraints and
 * soft_constraints. These convert between the two, so a solution can be
 * tried on the scheduler input exactly as it would be applied to the request.
 */
final class RequestShape
{
    private const TOP_LEVEL = ['candidates_per_invigilator'];

    public static function fromInput(array $input): array
    {
        $request = [
            ...($input['hard_constraints'] ?? []),
            ...($input['soft_constraints'] ?? []),
        ];

        foreach (self::TOP_LEVEL as $key) {
            if (array_key_exists($key, $input)) {
                $request[$key] = $input[$key];
            }
        }

        return $request;
    }

    /** Puts the constraints of a request back into a scheduler input. */
    public static function intoInput(array $input, array $request): array
    {
        foreach (['hard_constraints', 'soft_constraints'] as $group) {
            foreach (array_keys($input[$group] ?? []) as $key) {
                // Joint course periods come from the database, not from the request.
                if ($key !== 'required_joint_course_periods' && !array_key_exists($key, $request)) {
                    unset($input[$group][$key]);
                }
            }
        }

        foreach ($request as $key => $value) {
            if (in_array($key, self::TOP_LEVEL, true)) {
                $input[$key] = $value;
                continue;
            }
            $group = in_array($key, ['operational_period', 'session_duration', 'required_joint_course_periods'], true)
                ? 'hard_constraints'
                : 'soft_constraints';
            $input[$group][$key] = $value;
        }

        return $input;
    }
}
