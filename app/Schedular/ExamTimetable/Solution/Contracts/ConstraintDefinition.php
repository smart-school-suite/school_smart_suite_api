<?php

namespace App\Schedular\ExamTimetable\Solution\Contracts;

use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Remedy;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

/**
 * How one constraint or blocker type is explained and fixed.
 * A definition may answer to a constraint key, a violation key, or both.
 */
interface ConstraintDefinition
{
    /** @return string[] constraint and violation keys this definition handles */
    public function keys(): array;

    /** A locked constraint is never moved, resized or removed. */
    public function isLocked(): bool;

    public function describe(Subject $subject, ?SolutionContext $context = null): string;

    public function shortName(Subject $subject, ?SolutionContext $context = null): string;

    /** Headline of a failed request. */
    public function title(Subject $subject, ?SolutionContext $context = null): string;

    /** Why an obstacle is in the way. */
    public function reason(Subject $obstacle, ?SolutionContext $context = null): string;

    /** @return Remedy[] changes to the failed request itself */
    public function adjustRequest(Issue $issue, SolutionContext $context): array;

    /**
     * @param Issue[] $issues the issues this obstacle blocks
     * @return Remedy[] changes that take the obstacle out of the way
     */
    public function clearObstacle(Subject $obstacle, array $issues, SolutionContext $context): array;

    /**
     * Returns the generation request with one solution step applied.
     *
     * @throws \App\Schedular\ExamTimetable\Solution\Exceptions\CannotApplyRemedy
     */
    public function applyToRequest(array $request, array $step, RequestEdit $edit): array;
}
