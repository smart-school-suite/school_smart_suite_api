<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use Illuminate\Support\Facades\DB;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;

class HallRequestedTimeSlotViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return HallRequestedTimeSlot::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $hall = DB::table('halls')->where('id', $suggestedValue['hall_id'])->first();
                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", HallRequestedTimeSlot::KEY)->first() ?? null,
                    summary: "Schedule a period in {$hall->name} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']}",
                    context: [
                        'hall_id'  => $suggestedValue['course_id'],
                        'day'        => $suggestedValue['day'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time'   => $suggestedValue['end_time'],
                    ]
                );
            })
            ->all();
    }
}
