<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\ConstraintSuggestionDTO;
use App\Models\Constraint\SemTimetableConstraint;
use Illuminate\Support\Facades\DB;

class HallRequestedTimeWindowSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return HallRequestedTimeWindow::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $hall = DB::table('halls')->where('id', $suggestedValue['hall_id'])->first();
                return new ConstraintSuggestionDTO(
                    constraint: SemTimetableConstraint::where("key", HallRequestedTimeWindow::KEY)->first() ?? null,
                    summary: "Modify Hall Requested Time Slot {$hall->name} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']}",
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
