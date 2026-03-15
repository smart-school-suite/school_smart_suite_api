<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class DailyFreePeriodViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $minFrequencySuggestion = "Schedule A free period on {$suggestedValue['day']} at least {$suggestedValue['min_frequency']} times a day to satisfy the course's daily frequency requirement.";
                $maxFrequencySuggestion = "Schedule A free period on {$suggestedValue['day']} at most {$suggestedValue['max_frequency']} times a day to satisfy the course's daily frequency requirement.";
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
