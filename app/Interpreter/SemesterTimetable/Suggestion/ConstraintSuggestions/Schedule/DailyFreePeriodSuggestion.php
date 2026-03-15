<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class DailyFreePeriodSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $minFrequencySuggestion = "Modify the free period on {$suggestedValue['day']} at least {$suggestedValue['min_frequency']} times a day to satisfy the course's daily frequency requirement.";
                $maxFrequencySuggestion = "Modify free period on {$suggestedValue['day']} at most {$suggestedValue['max_frequency']} times a day to satisfy the course's daily frequency requirement.";
                return new BlockerSuggestionDTO(
                    summary: $suggestedValue['min_frequency'] ? $minFrequencySuggestion : $maxFrequencySuggestion,
                    context: [
                        'day'  => $suggestedValue['day'],
                        ...($suggestedValue['min_frequency'] ? ['min_frequency' => $suggestedValue['min_frequency']] : []),
                        ...($suggestedValue['max_frequency'] ? ['max_frequency' => $suggestedValue['max_frequency']] : []),
                    ]
                );
            })
            ->all();
    }
}
