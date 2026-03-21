<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\ConstraintSuggestionDTO;
use App\Models\Constraint\SemTimetableConstraint;
use Illuminate\Support\Facades\DB;
class CourseDailyFrequencySuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return CourseDailyFrequency::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $course = DB::table('courses')->where('id', $suggestedValue['course_id'])->first();
                $minFrequencySuggestion = "Modify  {$course->course_title} min course frequency to {$suggestedValue['min_frequency']} ";
                $maxFrequencySuggestion = "Modify {$course->course_title} max course frequency to  {$suggestedValue['max_frequency']} ";
                return new ConstraintSuggestionDTO(
                    constraint: SemTimetableConstraint::where("key", CourseDailyFrequency::KEY)->first() ?? null,
                    summary: $suggestedValue['min_frequency'] ? $minFrequencySuggestion : $maxFrequencySuggestion,
                    context: [
                        'course_id'  => $suggestedValue['course_id'],
                        ...($suggestedValue['min_frequency'] ? ['min_frequency' => $suggestedValue['min_frequency']] : []),
                        ...($suggestedValue['max_frequency'] ? ['max_frequency' => $suggestedValue['max_frequency']] : []),
                    ]
                );
            })
            ->all();
    }
}
