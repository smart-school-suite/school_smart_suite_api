<?php

namespace App\Schedular\ExamTimetable\Solution\Contracts;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

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

    /**
     * Returns the generate request with one solution step applied.
     *
     * @throws \App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy
     */
    public function applyToRequest(array $request, array $step, RequestEdit $edit): array;
}
