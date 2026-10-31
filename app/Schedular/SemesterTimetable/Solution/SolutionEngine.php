<?php

namespace App\Schedular\SemesterTimetable\Solution;

use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\SolutionPath;
use App\Schedular\SemesterTimetable\Solution\Planning\PathPlanner;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class SolutionEngine
{
    public function __construct(private PathPlanner $planner) {}

    public function generate(array $diagnostics, array $payload, array $grid, bool $withPreference): array
    {
        $context = new SolutionContext($payload, $grid, $withPreference);
        $issues = [];

        foreach ([Issue::HARD, Issue::SOFT] as $severity) {
            foreach ($diagnostics[$severity] ?? [] as $diagnostic) {
                if ($issue = Issue::fromDiagnostic($diagnostic, $severity)) {
                    $issues[] = $issue;
                }
            }
        }

        $byDay = [];
        foreach ($this->planner->plan($issues, $context) as $path) {
            $byDay[$path->day][] = $path->toArray();
        }

        $order = array_flip([...$context->days(), 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
        uksort($byDay, fn($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        return $byDay;
    }

    public static function options(array $suggestions): array
    {
        return collect($suggestions)
            ->map(fn(array $paths) => collect($paths)
                ->flatMap(fn(array $path) => $path['steps'])
                ->unique('id')
                ->values()
                ->all())
            ->filter()
            ->all();
    }
}
