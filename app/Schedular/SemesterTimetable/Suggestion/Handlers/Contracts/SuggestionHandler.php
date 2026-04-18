<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts;

use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;

interface SuggestionHandler
{
    public function supports(string $type): string;
    public function generate(Node $node, array $blockers = []): array;
    public function isExclusive(): bool;
    public function allowedActions(): array;
}
