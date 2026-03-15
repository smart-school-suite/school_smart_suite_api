<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use Illuminate\Support\Facades\DB;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
class CourseDailyFrequencyViolationSuggestion implements BlockerSuggestion
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
                $minFrequencySuggestion = "Schedule {$course->course_title} at least {$suggestedValue['min_frequency']} times a day to satisfy the course's daily frequency requirement.";
                $maxFrequencySuggestion = "Schedule {$course->course_title} at most {$suggestedValue['max_frequency']} times a day to satisfy the course's daily frequency requirement.";
                return new BlockerSuggestionDTO(
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
