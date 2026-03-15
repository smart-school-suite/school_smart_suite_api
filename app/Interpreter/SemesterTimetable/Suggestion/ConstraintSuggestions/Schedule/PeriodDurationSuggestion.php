<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class PeriodDurationSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return PeriodDuration::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                return new BlockerSuggestionDTO(
                    summary: "Modify the period duration on {$suggestedValue['day']} to {$suggestedValue['duration_minutes']}",
                    context: [
                        'day'        => $suggestedValue['day'],
                        'duration_minutes' => $suggestedValue['duration_minutes']
                    ]
                );
            })
            ->all();
    }
}
