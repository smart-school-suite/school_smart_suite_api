<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse as Constraint;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;

class RequiredJointCourseDefinition extends AbstractDefinition
{
    protected function name(): string
    {
        return 'Joint course';
    }

    public function isLocked(): bool
    {
        return true;
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return RequiredJointCourseDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequiredJointCourseBlocker::class;
    }
}
