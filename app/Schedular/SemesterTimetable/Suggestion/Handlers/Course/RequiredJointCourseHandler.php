<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Course;

use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse as RequiredJointCourseViolation;
use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse as RequiredJointCourseConstraint;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;

class RequiredJointCourseHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === RequiredJointCourseConstraint::KEY || $type === RequiredJointCourseViolation::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function allowedActions(): array
    {
        return ["keep"];
    }

    public function conflictOptions($constraint): array
    {
        return [];
    }

    public function dependencyOptions($constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
