<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\RequiredJointCourseBlocker;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * Joint course sessions are shared with other classes and set up on the joint course itself, so the planner
 * never moves them: only what is in their way changes.
 */
class RequiredJointCourseDefinition extends AbstractDefinition
{
    public function constraintKey(): ?string
    {
        return RequiredJointCourseSession::KEY;
    }

    public function violationKey(): ?string
    {
        return RequiredJointCourse::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return RequiredJointCourseDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequiredJointCourseBlocker::class;
    }

    public function isLocked(): bool
    {
        return true;
    }

    public function priority(): int
    {
        return 5;
    }

    protected function name(): string
    {
        return 'Joint course exam';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The joint ' . Text::course($subject, $context) . ' exam on ' . Text::when($subject) . ' could not be held';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The joint ' . Text::course($obstacle, $context) . ' exam takes ' . Text::when($obstacle) . ' and cannot move';
    }
}
