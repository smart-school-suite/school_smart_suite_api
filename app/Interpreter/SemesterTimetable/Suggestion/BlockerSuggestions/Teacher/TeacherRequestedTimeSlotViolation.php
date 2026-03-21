<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;
use Illuminate\Support\Facades\DB;

class TeacherRequestedTimeSlotViolation implements BlockerSuggestion
{
    public static function type(): string
    {
        return TeacherRequestedTimeSlot::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", TeacherRequestedTimeSlot::KEY)->first() ?? null,
                    summary: "Ajust {$teacher->name} requested time slot to {$suggestedValue['day']} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']}",
                    context: [
                        'day'  => $suggestedValue['day'],
                        'teacher_id' => $suggestedValue['teacher_id'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time' => $suggestedValue['end_time']
                    ]
                );
            })
            ->all();
    }
}
