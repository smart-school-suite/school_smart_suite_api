<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\ConstraintSuggestionDTO;
use App\Models\Constraint\SemTimetableConstraint;
use Illuminate\Support\Facades\DB;

class CourseRequestedTimeSlotSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return CourseRequestedSlot::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $course = DB::table('courses')->where('id', $suggestedValue['course_id'])->first();
                return new ConstraintSuggestionDTO(
                    constraint: SemTimetableConstraint::where("key", CourseRequestedSlot::KEY)->first() ?? null,
                    summary: "Modify Course Requested Time Slot {$course->course_title} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']}",
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
