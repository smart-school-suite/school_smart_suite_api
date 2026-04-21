<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class SuggestionEngine
{
    public function generate(array $diagnostics): array
    {
        $grouped = collect($diagnostics)
            ->groupBy(function ($diagnostic) {
                return $diagnostic->constraint_failed['details']['day'] ?? 'unknown';
            })
            ->toArray();

        $results = [];

        foreach ($grouped as $day => $dayDiagnostics) {
            $processor = new DayProcessor();
            $results[$day] = $processor->process($dayDiagnostics, $day);
        }

        return $results;
    }
}
