<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Student;

use App\Constant\Violation\ExamTimetable\Student\CandidateBusy as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/**
 * Candidates of an exam already sit another exam at that time: another exam
 * of this timetable they share, or one in another active timetable. The
 * other timetable is not changed from here, so the remedy is to move the
 * exam (offered by the request's own definition) or a manual step.
 */
class CandidateBusyDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Candidates sit another exam';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        $count = (int) $obstacle->get('student_count', 0);
        $who = $count === 1 ? '1 candidate' : ($count > 1 ? "{$count} candidates" : 'Some candidates');
        $busyCourse = $obstacle->get('busy_course_id');
        $what = $busyCourse
            ? 'the ' . ($context?->courseName($busyCourse) ?? 'another') . ' exam'
            : 'another exam';

        if (!$obstacle->date()) {
            return "{$who} of this exam sit other exams at every time left in the period";
        }

        return "{$who} of this exam already sit {$what} on " . Text::date($obstacle->date())
            . ($obstacle->get('start_time') ? ' at ' . Text::time($obstacle->get('start_time'), $obstacle->get('end_time')) : '');
    }

    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array
    {
        return [$this->review($obstacle, 'Move the other exam these candidates sit, or extend the resit period, then generate again')];
    }
}
