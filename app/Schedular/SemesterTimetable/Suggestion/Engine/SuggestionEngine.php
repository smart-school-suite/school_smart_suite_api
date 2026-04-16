<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class SuggestionEngine
{
    public function generate(array $diagnostics): array
    {
        $grouped = collect($diagnostics)->groupBy('day');

        $results = [];

        foreach ($grouped as $day => $dayDiagnostics) {
            $processor = new DayProcessor();
            $results[$day] = $processor->process($day, $dayDiagnostics->toArray());
        }

        return $results;
    }
}
