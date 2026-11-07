<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as Constraint;
use App\Constant\Violation\ExamTimetable\Hall\HallRequestedSlot as Violation;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\Definitions\Concerns\AttachesToSessions;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class HallRequestedSlotDefinition extends AbstractRequestDefinition
{
    use AttachesToSessions;

    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Hall request';
    }

    protected function canJoin(Subject $target, array $session, SolutionContext $context): bool
    {
        $hallId = $target->get('hall_id');
        if (!$hallId || in_array($hallId, array_column($session['halls'], 'hall_id'), true)) {
            return false;
        }

        return $context->freeHalls(
            $session['date'],
            Time::toMinutes($session['start_time']),
            Time::toMinutes($session['end_time']),
            [],
            $session['course_id']
        )->contains('hall_id', $hallId);
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        return fn() => false;
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::hall($subject, $context)) . ' could not be used on ' . Text::when($subject);
    }

    protected function requestKey(): string
    {
        return Constraint::KEY;
    }

    protected function identityFields(): array
    {
        return ['hall_id'];
    }
}
