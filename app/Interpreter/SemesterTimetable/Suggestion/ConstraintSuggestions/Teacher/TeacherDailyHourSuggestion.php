<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use Illuminate\Support\Facades\DB;

class TeacherDailyHourSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return TeacherDailyHours::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                $minDailyHourSuggestion = "Modify the teacher min daily hours for  {$teacher->name}  to {$suggestedValue['min_daily_hours']}";
                $maxDailyHourSuggestion = "Modify the teacher min daily hours for {$suggestedValue['max_daily_hours']}";
                return new BlockerSuggestionDTO(
                    summary: $suggestedValue['min_daily_hours'] ? $minDailyHourSuggestion : $maxDailyHourSuggestion,
                    context: [
                        'day'  => $suggestedValue['day'],
                        ...($suggestedValue['min_daily_hours'] ? ['min_daily_hours' => $suggestedValue['min_daily_hours']] : []),
                        ...($suggestedValue['max_daily_hours'] ? ['max_daily_hours' => $suggestedValue['max_daily_hours']] : []),
                    ]
                );
            })
            ->all();
    }
}
