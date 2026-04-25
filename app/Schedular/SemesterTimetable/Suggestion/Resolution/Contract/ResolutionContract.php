<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract;

interface ResolutionContract
{
    public function supports(string $type): bool;
    public function resolve($resolution, $params): array;
}
