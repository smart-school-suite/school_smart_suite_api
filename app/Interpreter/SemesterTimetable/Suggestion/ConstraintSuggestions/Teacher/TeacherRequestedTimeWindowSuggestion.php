<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use Illuminate\Support\Facades\DB;

class TeacherRequestedTimeWindowSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return TeacherRequestedTimeSlot::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                return new BlockerSuggestionDTO(
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
