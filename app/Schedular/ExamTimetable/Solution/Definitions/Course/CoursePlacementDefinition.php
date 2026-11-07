<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\CoursePlacement as Constraint;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\ChangesExamSchedule;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * A course that fits nowhere. Its blockers say what was missing (seats,
 * invigilators, time) and offer the fixes; giving the exam more time is
 * offered here as well, since every missing resource gets easier with more slots.
 */
class CoursePlacementDefinition extends AbstractDefinition
{
    use ChangesExamSchedule;

    public function keys(): array
    {
        return [Constraint::KEY];
    }

    protected function name(): string
    {
        return 'Course exam';
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::course($subject, $context)) . ' could not be given an exam';
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        $duration = (int) $issue->subject->get('duration', 0);
        return $duration > 0 ? $this->moreExamTime($issue->subject, $context, $duration) : [];
    }

    public function applyToRequest(array $request, array $step, RequestEdit $edit): array
    {
        return $this->applySchedule($request, $step);
    }
}
