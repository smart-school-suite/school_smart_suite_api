<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotConstraint;
use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;

class CourseRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === CourseRequestedSlotConstraint::KEY || $type === CourseRequestedSlotBlocker::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function allowedActions(): array
    {
        return ["keep", "modify", "remove"];
    }

    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'remove',
                label: 'Remove Course Requested Slot'
            ),
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move Requested Slot  to another time',
                meta: ['field' => 'time']
            )
        ];
    }

    public function dependencyOptions($constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }

}
