<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Teacher;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherWeeklyHours;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;
use Illuminate\Support\Facades\DB;
class TeacherWeeklyHourViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return TeacherWeeklyHours::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();
                $minweeklyHourSuggestion = "Adjust {$teacher->name} minimum weekly hours to {$suggestedValue['min_weekly_hours']}";
                $maxweeklyHourSuggestion = "Adjust {$teacher->name} maximum weekly hours to {$suggestedValue['max_weekly_hours']}";
                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", TeacherWeeklyHours::KEY)->first() ?? null,
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
