<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use Illuminate\Support\Facades\DB;

class TeacherDailyHourViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return TeacherDailyHours::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                $minDailyHourSuggestion = "Adjust {$teacher->name} minimum daily hours to {$suggestedValue['min_daily_hours']}";
                $maxDailyHourSuggestion = "Adjust {$teacher->name} maximum daily hours to {$suggestedValue['max_daily_hours']}";
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
