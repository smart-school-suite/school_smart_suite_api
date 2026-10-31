<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse as Constraint;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

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

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The joint course ' . Text::course($subject, $context) . ' on ' . Text::when($subject) . ' doesn\'t fit the timetable';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The joint course ' . Text::course($obstacle, $context) . ' is fixed at ' . Text::period($obstacle) . ' and cannot be moved';
    }
}
