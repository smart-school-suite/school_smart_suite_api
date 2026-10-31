<?php

namespace App\Schedular\SemesterTimetable\Solution\Contracts;

use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Remedy;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

interface ConstraintDefinition
{
    public function constraintKey(): ?string;

    public function violationKey(): ?string;

    public function diagnosticBuilder(): ?string;

    public function blockerBuilder(): ?string;

    public function isLocked(): bool;

    public function priority(): int;

    public function describe(Subject $subject, ?SolutionContext $context = null): string;

    public function shortName(Subject $subject, ?SolutionContext $context = null): string;

    public function title(Subject $subject, ?SolutionContext $context = null): string;

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string;

    /** @return Remedy[] */
    public function adjustRequest(Issue $issue, SolutionContext $context): array;

    /**
     * @param Issue[] $issues
     * @return Remedy[]
     */
    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array;
}
