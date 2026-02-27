<?php

namespace App\Interpreter\SemesterTimetable\Suggestion\Core;

class SuggestionRegistry
{
    protected array $generators = [

    ];

    public function forBlocker(string $blockerType): array
    {
        return collect($this->generators)
            ->map(fn($class) => app($class))
            ->filter(fn($generator) => $generator::supports($blockerType))
            ->values()
            ->all();
    }
}
