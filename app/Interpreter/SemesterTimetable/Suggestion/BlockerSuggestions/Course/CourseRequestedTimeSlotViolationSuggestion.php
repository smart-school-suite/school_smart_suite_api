<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use Illuminate\Support\Facades\DB;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;

class CourseRequestedTimeSlotViolationSuggestion implements BlockerSuggestion
{

    public static function type(): string
    {
        return CourseRequestedSlot::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $course = DB::table('courses')->where('id', $suggestedValue['course_id'])->first();
                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", CourseRequestedSlot::KEY)->first() ?? null,
                    summary: "Schedule {$course->course_title} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']} to satisfy a requested time slot.",
                    context: [
                        'course_id'  => $suggestedValue['course_id'],
                        'day'        => $suggestedValue['day'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time'   => $suggestedValue['end_time'],
                    ]
                );
            })
            ->all();
    }
}
