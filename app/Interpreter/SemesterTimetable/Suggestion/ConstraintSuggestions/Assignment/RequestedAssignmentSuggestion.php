<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\ConstraintSuggestions\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\ConstraintSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use Illuminate\Support\Facades\DB;

class RequestedAssignmentSuggestion implements ConstraintSuggestion
{
    public static function type(): string
    {
        return RequestedAssignment::KEY;
    }

    public function suggest(array $constraintModification): array
    {
        return collect($constraintModification['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $course = DB::table('courses')->where('id', $suggestedValue['course_id'])->first();
                $hall = DB::table('halls')->where('id', $suggestedValue['hall_id'])->first();
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();

                return new BlockerSuggestionDTO(
                    summary: "Modify Requested Assignment to {$suggestedValue['day']} from {$suggestedValue['start_time']}  to {$suggestedValue['end_time']} in hall {$hall->nmae} for course  {$course->course_title} by teacher {$teacher->name}",
                    context: [
                        'teacher_id' => $suggestedValue['teacher_id'],
                        'course_id'  => $suggestedValue['course_id'],
                        'day'        => $suggestedValue['day'],
                        'start_time' => $suggestedValue['start_time'],
                        'end_time'   => $suggestedValue['end_time'],
                        'hall_id'    => $suggestedValue['hall_id'],
                    ]
                );
            })
            ->all();
    }
}
