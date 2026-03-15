<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class BreakPeriodSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return BreakPeriod::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                return new BlockerSuggestionDTO(
                    summary: "Schedule Break on {$suggestedValue['day']} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']}",
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
