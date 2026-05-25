<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract;

use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

interface BlockerSuggestion
{
    public function support(string $blockerType): bool;
    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO;
}
