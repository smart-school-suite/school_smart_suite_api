<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Solution\Contract;

interface SolutionPath
{
    public function supports(string $targetType): bool;
    public function getSolution(object $resolution, array $resolvedSteps = []): array;
}
