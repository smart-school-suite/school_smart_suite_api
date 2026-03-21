<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\BlockerSuggestions\Assignment;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Interpreter\SemesterTimetable\Suggestion\Contracts\BlockerSuggestion;
use App\Interpreter\SemesterTimetable\Suggestion\DTO\BlockerSuggestionDTO;
use App\Models\Constraint\SemTimetableBlocker;
use Illuminate\Support\Facades\DB;

class RequestedAssignmentViolationSuggestion implements BlockerSuggestion
{
    public static function type(): string
    {
        return RequestedAssigment::KEY;
    }

    public function suggest(array $blockerResolution): array
    {
        return collect($blockerResolution['suggested_values'] ?? [])
            ->map(function ($suggestedValue) {
                $course = DB::table('courses')->where('id', $suggestedValue['course_id'])->first();
                $hall = DB::table('halls')->where('id', $suggestedValue['hall_id'])->first();
                $teacher = DB::table('teachers')->where('id', $suggestedValue['teacher_id'])->first();

                return new BlockerSuggestionDTO(
                    blocker: SemTimetableBlocker::where("key", RequestedAssigment::KEY)->first() ?? null,
                    summary: "Schedule {$course->name} from {$suggestedValue['start_time']} to {$suggestedValue['end_time']} on {$suggestedValue['day']} for {$teacher->name} in {$hall->name} to satisfy a requested assignment.",
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
