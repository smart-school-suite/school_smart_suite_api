<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherWeeklyHours;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\ConstraintSuggestionDTO;
use App\Models\Constraint\SemTimetableConstraint;
use Illuminate\Support\Facades\DB;

class TeacherWeeklyHourSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return TeacherWeeklyHours::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                $minweeklyHourSuggestion = "Modify the min weekly hours for  {$teacher->name} minimum weekly hours to {$suggestedValue['min_weekly_hours']}";
                $maxweeklyHourSuggestion = "Modify the max weekly hours for  {$teacher->name} maximum weekly hours to {$suggestedValue['max_weekly_hours']}";
                return new ConstraintSuggestionDTO(
                    constraint: SemTimetableConstraint::where("key", TeacherWeeklyHours::KEY)->first() ?? null,
                    summary: $suggestedValue['min_weekly_hours'] ? $minweeklyHourSuggestion : $maxweeklyHourSuggestion,
                    context: [
                        'day'  => $suggestedValue['day'],
                        ...($suggestedValue['min_weekly_hours'] ? ['min_weekly_hours' => $suggestedValue['min_weekly_hours']] : []),
                        ...($suggestedValue['max_weekly_hours'] ? ['max_weekly_hours' => $suggestedValue['max_weekly_hours']] : []),
                    ]
                );
            })
            ->all();
    }
}
