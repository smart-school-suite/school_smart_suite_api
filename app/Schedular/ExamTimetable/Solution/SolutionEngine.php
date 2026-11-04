<?php

namespace App\Schedular\ExamTimetable\Solution;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\SolutionPath;
use App\Schedular\ExamTimetable\Solution\Planning\PathPlanner;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

class SolutionEngine
{
    public function __construct(private PathPlanner $planner) {}

    /**
     * @return array solution paths grouped by exam date (Y-m-d), with "unscheduled" last
     */
    public function generate(array $diagnostics, array $payload, ?State $state = null): array
    {
        $context = new SolutionContext($payload, $state);
        $issues = [];

        // When generation failed only the blocking problems are planned; warnings come after a successful run.
        $severities = empty($diagnostics[Issue::HARD]) ? [Issue::SOFT] : [Issue::HARD];

        foreach ($severities as $severity) {
            foreach ($diagnostics[$severity] ?? [] as $diagnostic) {
                if ($issue = Issue::fromDiagnostic($diagnostic, $severity)) {
                    $issues[] = $issue;
                }
            }
        }

        $paths = collect($this->planner->plan($issues, $context))->sortBy([
            fn(SolutionPath $a, SolutionPath $b) => $b->resolvedCount() <=> $a->resolvedCount(),
            fn(SolutionPath $a, SolutionPath $b) => ($b->verified === true) <=> ($a->verified === true),
            fn(SolutionPath $a, SolutionPath $b) => $a->cost <=> $b->cost,
            fn(SolutionPath $a, SolutionPath $b) => count($a->steps) <=> count($b->steps),
        ]);

        $byDate = [];
        foreach ($paths as $path) {
            $byDate[$path->date][] = $path->toArray();
        }

        uksort($byDate, fn($a, $b) => [$a === Issue::UNSCHEDULED, $a] <=> [$b === Issue::UNSCHEDULED, $b]);

        return $byDate;
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
