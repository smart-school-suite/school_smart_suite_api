<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts;


interface SuggestionHandler
{
    public function supports(string $type): string;
    public function conflictOptions(array $constraint): array;
    public function dependencyOptions(array $constraint, array $blockers): array;
    public function isExclusive(): bool;
    public function allowedActions(): array;
}
