<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract;

use App\Schedular\SemesterTimetable\Suggestion\DTO\ResolutionDTO;

interface ResolutionContract
{
    public function supports(string $type): bool;
    public function resolve(ResolutionDTO $resolution, array $params): array;
}
