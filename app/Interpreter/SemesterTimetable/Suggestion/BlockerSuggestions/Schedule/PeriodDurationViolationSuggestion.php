<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class PeriodDurationViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return PeriodDuration::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
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
