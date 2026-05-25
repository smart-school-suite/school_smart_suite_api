<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Contracts;

use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;

interface ResolverContract
{
    public function supports(string $type): bool;
    public function normalize(ScenarioDTO $scenario);
}
