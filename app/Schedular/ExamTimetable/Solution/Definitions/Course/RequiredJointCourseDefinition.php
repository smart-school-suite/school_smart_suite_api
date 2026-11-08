<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession as Constraint;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Joint course exams are shared with other specialties, so their time is never changed here. */
class RequiredJointCourseDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Joint course exam';
    }

    public function isLocked(): bool
    {
        return true;
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The joint course exam ' . Text::course($subject, $context) . ' on ' . Text::when($subject) . ' does not fit the timetable';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'The joint course exam ' . Text::course($obstacle, $context) . ' is fixed at ' . Text::period($obstacle) . ' and cannot be moved';
    }
}
