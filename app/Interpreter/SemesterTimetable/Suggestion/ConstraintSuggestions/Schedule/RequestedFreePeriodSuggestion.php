<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
class RequestedFreePeriodSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return RequestedFreePeriod::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                return new BlockerSuggestionDTO(
                    summary: "Modify the requested free period constraint to {$suggestedValue['day']} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']}",
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
