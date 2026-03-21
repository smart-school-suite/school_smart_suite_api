<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;

class RequestedFreePeriodViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return RequestedFreePeriod::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", RequestedFreePeriod::KEY)->first() ?? null,
                    summary: "Adjust the free period to {$suggestedValue['day']} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']}",
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
