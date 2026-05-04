<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as TeacherRequestedTimeSlotBlocker;
use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as TeacherRequestedTimeSlotConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use Illuminate\Support\Str;

class TeacherRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === TeacherRequestedTimeSlotBlocker::KEY || $type === TeacherRequestedTimeSlotConstraint::KEY;
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
        $metaData = [...$constraint["details"], "type" => $constraint["type"]];
        return [
            new SuggestionOptionDTO(
                action: 'remove',
                label: 'Remove Teacher Requested Slot',
                meta: $metaData,
                proposals: [
                    "id" => Str::uuid()->toString(),
                    ...$metaData
                ]
            ),
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move Teacher Requested Slot to another time',
                meta: $metaData,
            )
        ];
    }

    public function dependencyOptions(array $constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
