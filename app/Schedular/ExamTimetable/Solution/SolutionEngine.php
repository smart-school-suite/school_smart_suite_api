<?php

namespace App\Schedular\ExamTimetable\Solution;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\SolutionPath;
use App\Schedular\ExamTimetable\Solution\Planning\PathPlanner;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;

/**
 * Works out ways to fix what an exam timetable run could not satisfy.
 *
 * When any hard constraint failed, only hard issues get solution paths (the
 * timetable is unusable until they are fixed). Otherwise the soft issues do.
 * Paths are grouped by date; issues without a date are under "general".
 */
class SolutionEngine
{
    public function __construct(private PathPlanner $planner) {}

    /**
     * @param array $diagnostics ['hard' => [diagnostic arrays], 'soft' => [...]]
     * @return array<string, array[]> date => solution paths
     */
    public function generate(array $diagnostics, array $payload, ?State $state = null): array
    {
        $context = new SolutionContext($payload, $state);

        $all = [];
        foreach ([Issue::HARD, Issue::SOFT] as $severity) {
            foreach ($diagnostics[$severity] ?? [] as $diagnostic) {
                if ($issue = Issue::fromDiagnostic($diagnostic, $severity)) {
                    $all[] = $issue;
                }
            }
        }

        $severity = collect($all)->contains(fn(Issue $issue) => $issue->severity === Issue::HARD) ? Issue::HARD : Issue::SOFT;
        $issues = array_values(array_filter($all, fn(Issue $issue) => $issue->severity === $severity));

        if (empty($issues)) {
            return [];
        }

        $byDate = [];
        foreach ($this->planner->plan($issues, $all, $context) as $path) {
            /** @var SolutionPath $path */
            $byDate[$path->date][] = $path->toArray();
        }

        uksort($byDate, fn(string $a, string $b) => [$a === Issue::NO_DATE ? 0 : 1, $a] <=> [$b === Issue::NO_DATE ? 0 : 1, $b]);

        return $byDate;
    }
}
