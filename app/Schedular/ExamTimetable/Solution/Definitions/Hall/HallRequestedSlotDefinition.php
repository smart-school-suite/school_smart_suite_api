<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Hall\HallRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\Support\MovesToExamSessions;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class HallRequestedSlotDefinition extends AbstractRequestDefinition
{
    use MovesToExamSessions;

    public function constraintKey(): ?string
    {
        return HallRequestedSlot::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return HallRequestedSlotDiagnostic::class;
    }

    protected function name(): string
    {
        return 'Hall request';
    }

    protected function requestKey(): string
    {
        return HallRequestedSlot::KEY;
    }

    protected function identityFields(): array
    {
        return ['hall_id'];
    }

    protected function occupiesStudents(): bool
    {
        return false;
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return Text::hall($subject, $context) . ' could not be used on ' . Text::when($subject) . ' as requested';
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $id = $target->get('hall_id');

        return fn(string $date, int $start, int $end) => $context->hallFits($id, $date, $start, $end, 1);
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return $this->movesToSessions($issue->subject, $context, $this->accepts($issue->subject, $context));
    }
}
