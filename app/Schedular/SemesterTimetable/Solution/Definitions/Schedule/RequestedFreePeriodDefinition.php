<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as Constraint;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule\RequestedFreePeriodBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\RequestedFreePeriodDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

class RequestedFreePeriodDefinition extends AbstractRequestDefinition
{
    protected function name(): string
    {
        return 'Requested free period';
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
        return RequestedFreePeriodDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequestedFreePeriodBlocker::class;
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        return fn(string $day, int $start, int $end) => true;
    }

    protected function occupiesWith(Subject $target): array
    {
        return ['exclusive' => true];
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return 'The free period on ' . Text::when($subject) . ' can\'t be kept';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'A free period is requested at ' . Text::period($obstacle);
    }
}
