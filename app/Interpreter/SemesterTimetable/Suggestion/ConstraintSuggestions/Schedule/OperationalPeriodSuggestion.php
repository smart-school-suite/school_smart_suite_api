<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\ConstraintSuggestionDTO;
use App\Models\Constraint\SemTimetableConstraint;

class OperationalPeriodSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                return new ConstraintSuggestionDTO(
                    constraint: SemTimetableConstraint::where("key", OperationalPeriod::KEY)->first() ?? null,
                    summary: "Ajust the operational Period on {$suggestedValue['day']} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} ",
                    context: [
                        'day'        => $suggestedValue['day'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time'   => $suggestedValue['end_time'],
                    ]
                );
            })
            ->all();
    }
}
