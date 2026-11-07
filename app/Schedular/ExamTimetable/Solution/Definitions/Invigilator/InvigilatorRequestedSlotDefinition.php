<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as Constraint;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorRequestedSlot as Violation;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\AttachesToSessions;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class InvigilatorRequestedSlotDefinition extends AbstractRequestDefinition
{
    use AttachesToSessions;

    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Invigilator request';
    }

    /** Only exams of courses the invigilator does not teach. */
    protected function canJoin(Subject $target, array $session, SolutionContext $context): bool
    {
        $invigilatorId = $target->get('invigilator_id');
        if (!$invigilatorId) {
            return false;
        }

        return $context->freeInvigilators(
            $session['date'],
            Time::toMinutes($session['start_time']),
            Time::toMinutes($session['end_time']),
            $session['course_id']
        )->contains('invigilator_id', $invigilatorId);
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        return fn() => false;
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::invigilator($subject, $context)) . ' could not invigilate on ' . Text::when($subject);
    }

    protected function requestKey(): string
    {
        return Constraint::KEY;
    }

    protected function identityFields(): array
    {
        return ['invigilator_id'];
    }
}
