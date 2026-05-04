<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Course;

use App\Constant\Action\AppActions;
use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotConstraint;
use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use Illuminate\Support\Str;
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

    public function conflictOptions(array $constraint): array
    {
        $metaData = [...$constraint["details"], "type" => $constraint["type"] ];
        return [
            new SuggestionOptionDTO(
                action: AppActions::REMOVE,
                label: 'Remove Course Requested Slot',
                meta: $metaData,
                proposals: [
                    "id" => Str::uuid()->toString(),
                    ...$metaData
                ]
            ),
            new SuggestionOptionDTO(
                action: AppActions::MODIFY,
                label: 'Move Requested Slot  to another time',
                meta: $metaData
            )
        ];
    }

    public function dependencyOptions(array $constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
