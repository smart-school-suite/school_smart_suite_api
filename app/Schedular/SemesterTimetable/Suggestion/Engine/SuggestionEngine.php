<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Engine;

class SuggestionEngine
{
    public function generate(array $diagnostics): array
    {
        $results = [];

        foreach ($diagnostics as $type => $list) {
            foreach ($list as $diagnostic) {
                $day = $diagnostic->constraint_failed['details']['day'] ?? 'unknown';
                $results[$day][$type][] = $diagnostic;
            }
        }

        foreach ($results as $day => $structuredDiagnostics) {
            $processor = new DayProcessor();
            //['hard' => [...], 'soft' => [...]]
            $results[$day] = $processor->process($structuredDiagnostics, $day);
        }

        return $results;
    }
}
