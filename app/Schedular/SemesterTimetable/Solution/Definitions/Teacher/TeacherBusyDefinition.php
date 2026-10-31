<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherBusyBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Collection;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;

class TeacherBusyDefinition extends AbstractResourceDefinition
{
    protected function name(): string
    {
        return 'Teacher busy';
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return TeacherBusyBlocker::class;
    }

    protected function field(): string
    {
        return 'teacher_id';
    }

    protected function resourceLabel(): string
    {
        return 'teacher';
    }

    protected function fixedFor(): array
    {
        return [TeacherRequestedTimeSlot::KEY];
    }

    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        $courseId = $issue->subject->get('course_id');

        if (!$courseId) {
            return collect();
        }

        return $context->freeTeachers($issue->day(), $window[0], $window[1], $courseId, array_filter([$current]));
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::teacher($obstacle, $context) . ' is already teaching elsewhere at ' . Text::period($obstacle);
    }
}
