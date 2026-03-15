<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;

class BreakPeriodViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return BreakPeriod::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
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
