<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use Illuminate\Support\Facades\DB;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
class HallBusySuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return HallBusy::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $hall = DB::table('halls')->where('id', $suggestedValue['hall_id'])->first();
                return new BlockerSuggestionDTO(
                    summary: "Schedule a period in {$hall->name} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']}",
                    context: [
                        'hall_id'  => $suggestedValue['hall_id'],
                        'day'        => $suggestedValue['day'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time'   => $suggestedValue['end_time'],
                    ]
                );
            })
            ->all();
    }
}
