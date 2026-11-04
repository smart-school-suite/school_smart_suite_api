<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Invigilator\InvigilatorRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\Support\MovesToExamSessions;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class InvigilatorRequestedSlotDefinition extends AbstractRequestDefinition
{
    use MovesToExamSessions;

    public function constraintKey(): ?string
    {
        return InvigilatorRequestedSlot::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return InvigilatorRequestedSlotDiagnostic::class;
    }

    protected function name(): string
    {
        return 'Invigilator request';
    }

    protected function requestKey(): string
    {
        return InvigilatorRequestedSlot::KEY;
    }

    protected function identityFields(): array
    {
        return ['invigilator_id'];
    }

    protected function occupiesStudents(): bool
    {
        return false;
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return Text::invigilator($subject, $context) . ' could not invigilate on ' . Text::when($subject) . ' as requested';
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $id = $target->get('invigilator_id');

        return fn(string $date, int $start, int $end) => $context->invigilatorFree($id, $date, $start, $end);
    }

    public function adjustRequest(Issue $issue, SolutionContext $context): array
    {
        return $this->movesToSessions($issue->subject, $context, $this->accepts($issue->subject, $context));
    }
}
