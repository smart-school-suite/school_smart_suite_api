<?php

namespace App\Services\ExamTimetable;

use App\Exceptions\AppException;
use App\Models\ExamTimetable\ExamTimetableError;
use App\Models\ExamTimetable\ExamTimetableRun;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

/**
 * Plain-language problems and solutions of one exam timetable version, grouped by exam date.
 * Mirrors TimetableInsightService of the semester timetable; "day" becomes "date", and problems that are not
 * tied to one date (courses that could not be scheduled) are grouped under "unscheduled".
 */
class ExamTimetableInsightService
{
    private const ACTION_LABELS = [
        'move' => 'Move',
        'modify' => 'Change',
        'replace' => 'Replace',
        'remove' => 'Remove',
        'relax' => 'Allow',
        'review' => 'Review',
    ];

    private const FIELD_LABELS = [
        'date' => 'Date',
        'time' => 'Time',
        'start_time' => 'Start time',
        'end_time' => 'End time',
        'invigilator_id' => 'Invigilator',
        'hall_id' => 'Hall',
        'duration' => 'Exam length',
        'max_sessions' => 'Maximum exams',
        'min_sessions' => 'Minimum exams',
        'excluded_dates' => 'Closed date',
    ];

    public function __construct(
        private ConstraintCatalog $catalog,
        private ApplyExamSolutionService $solutions,
    ) {}

    public function solutions(object $currentSchool, string $versionId): array
    {
        $run = $this->load($currentSchool, $versionId);
        $problems = $this->problems($run);

        return [
            'version' => $run['version'],
            'summary' => $this->summary($run),
            'dates' => $problems
                ->groupBy('date')
                ->map(fn(Collection $problems, string $date) => [
                    'date' => $date,
                    'date_label' => Text::date($date),
                    'issue_count' => $problems->sum(fn(array $problem) => count($problem['issues'])),
                    'problem_count' => $problems->count(),
                    'problems' => $problems->values()->all(),
                ])
                ->values()
                ->all(),
            'unscheduled_courses' => $run['unscheduled'],
            'setup_problems' => $run['issues']->where('type', 'setup')->values()->all(),
        ];
    }

    /** Every issue of the version in plain language, without options. */
    public function issues(object $currentSchool, string $versionId): array
    {
        return $this->load($currentSchool, $versionId)['issues']->values()->all();
    }

    private function load(object $currentSchool, string $versionId): array
    {
        $version = ExamTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);

        if (!$version) {
            throw new AppException(
                "Exam timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Exam Timetable Version Not Found',
                "We couldn't find this exam timetable version. It may have been deleted.",
                null
            );
        }

        $timetable = ExamTimetableRun::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();

        $setupErrors = $version->scheduler_status === ExamTimetableVersion::STATUS_FAILED
            ? ExamTimetableError::where('school_branch_id', $currentSchool->id)
                ->where('timetable_version_id', $versionId)
                ->orderBy('created_at', 'desc')
                ->first()
            : null;

        $diagnostics = [
            Issue::HARD => $this->asArray($timetable?->raw_diagnostics['hard'] ?? []),
            Issue::SOFT => $this->asArray($timetable?->raw_diagnostics['soft'] ?? []),
        ];
        $context = new SolutionContext($this->asArray($timetable?->scheduler_input ?? []));
        $paths = collect($this->asArray($timetable?->raw_suggestions ?? []))
            ->flatten(1)
            ->filter(fn($path) => is_array($path) && isset($path['issue_ids']))
            ->values();

        return [
            'version' => $version->only(['id', 'label', 'version_number', 'scheduler_status', 'exam_id']),
            'status' => $version->scheduler_status,
            'issues' => $this->issueList($diagnostics, $context, $paths)->concat($this->setupIssues($setupErrors)),
            'paths' => $paths,
            'context' => $context,
            'unscheduled' => $this->asArray($timetable?->unscheduled_courses ?? []),
            'request' => collect($this->asArray($timetable?->request_payload ?? []))->except('version_id')->all(),
            'edit' => $this->solutions->editFor($diagnostics),
        ];
    }

    private function issueList(array $diagnostics, SolutionContext $context, Collection $paths): Collection
    {
        $severities = empty($diagnostics[Issue::HARD]) ? [Issue::SOFT] : [Issue::HARD];

        return collect($severities)
            ->flatMap(fn($severity) => collect($diagnostics[$severity])
                ->map(fn($diagnostic) => Issue::fromDiagnostic($diagnostic, $severity))
                ->filter())
            ->unique(fn(Issue $issue) => $issue->id())
            ->map(fn(Issue $issue) => $this->presentIssue($issue, $context, $paths))
            ->sortBy(fn(array $issue) => [$issue['date'] === Issue::UNSCHEDULED ? '9999' : $issue['date'], $issue['time'] ?? ''])
            ->values();
    }

    private function presentIssue(Issue $issue, SolutionContext $context, Collection $paths): array
    {
        $subject = $issue->subject;
        $definition = $this->catalog->for($issue->type());
        $blocking = $issue->severity === Issue::HARD;

        return [
            'id' => $issue->id(),
            'type' => $issue->type(),
            'severity' => $blocking ? 'blocking' : 'warning',
            'severity_label' => $blocking ? 'Must be fixed before the exam timetable is complete' : 'This request could not be honoured',
            'date' => $issue->date(),
            'date_label' => Text::date($issue->date()),
            'time' => $subject->get('start_time') && $subject->get('end_time') ? Text::period($subject) : null,
            'title' => $definition->title($subject, $context),
            'reasons' => collect($issue->obstacles)
                ->map(fn(Subject $obstacle) => $this->catalog->for($obstacle->type)->reason($obstacle, $context))
                ->unique()
                ->values()
                ->all(),
            'affected' => $this->affected($subject, $context),
            'can_be_changed' => !$definition->isLocked(),
            'solution_count' => $paths->filter(fn($path) => in_array($issue->id(), $path['issue_ids'], true))->count(),
        ];
    }

    private function setupIssues(?object $errors): Collection
    {
        return collect($errors?->errors ?? [])->map(fn($error) => [
            'id' => md5(($error['title'] ?? '') . ($error['description'] ?? '')),
            'type' => 'setup',
            'severity' => 'blocking',
            'severity_label' => 'Must be fixed before the exam timetable can be generated',
            'date' => null,
            'date_label' => null,
            'time' => null,
            'title' => $error['title'] ?? 'Setup problem',
            'reasons' => array_values(array_filter([$error['description'] ?? null])),
            'affected' => [],
            'can_be_changed' => true,
            'solution_count' => 0,
            'fix_in' => $error['additional_params']['path'] ?? null,
        ]);
    }

    private function affected(Subject $subject, SolutionContext $context): array
    {
        return array_values(array_filter([
            $subject->get('course_id') ? ['type' => 'course', 'id' => $subject->get('course_id'), 'name' => Text::course($subject, $context)] : null,
            $subject->get('invigilator_id') ? ['type' => 'invigilator', 'id' => $subject->get('invigilator_id'), 'name' => Text::invigilator($subject, $context)] : null,
            $subject->get('hall_id') ? ['type' => 'hall', 'id' => $subject->get('hall_id'), 'name' => Text::hall($subject, $context)] : null,
        ]));
    }

    private function problems(array $run): Collection
    {
        $issues = $run['issues']->where('type', '!=', 'setup')->keyBy('id');
        $paths = $run['paths']->filter(fn($path) => collect($path['issue_ids'])->every(fn($id) => $issues->has($id)));

        $parent = $issues->keys()->mapWithKeys(fn($id) => [$id => $id])->all();
        $find = function ($id) use (&$parent, &$find) {
            return $parent[$id] === $id ? $id : $parent[$id] = $find($parent[$id]);
        };

        foreach ($paths as $path) {
            $first = $path['issue_ids'][0];
            foreach ($path['issue_ids'] as $id) {
                $parent[$find($id)] = $find($first);
            }
        }

        return $issues->keys()
            ->groupBy(fn($id) => $find($id))
            ->map(function (Collection $ids) use ($issues, $paths, $run) {
                $members = $ids->map(fn($id) => $issues[$id])->values();
                $options = $paths
                    ->filter(fn($path) => !array_diff($path['issue_ids'], $ids->all()))
                    ->values()
                    ->map(fn($path, $index) => $this->presentOption($path, $index, $members, $run))
                    ->all();
                $first = $members->first();

                return [
                    'id' => md5($ids->sort()->implode(',')),
                    'date' => $first['date'],
                    'date_label' => $first['date_label'],
                    'title' => $members->count() === 1
                        ? $first['title']
                        : ($first['date'] === Issue::UNSCHEDULED
                            ? $members->count() . ' courses could not be scheduled'
                            : $members->count() . ' connected problems on ' . $first['date_label']),
                    'description' => $members->count() === 1
                        ? implode('. ', $first['reasons'])
                        : 'These problems affect each other, so they are best fixed together.',
                    'issues' => $members->all(),
                    'option_count' => count($options),
                    'options' => $options,
                ];
            })
            ->sortBy(fn(array $problem) => $problem['date'] === Issue::UNSCHEDULED ? '0000' : $problem['date'])
            ->values();
    }

    private function presentOption(array $path, int $index, Collection $members, array $run): array
    {
        $context = $run['context'];
        [, $applied] = $this->solutions->assess($run['request'], $path, $run['edit']);
        $manual = collect($applied)->where('applied', false);
        $fixed = count($path['issue_ids']);

        return [
            'id' => $path['id'],
            'rank' => $index + 1,
            'recommended' => $index === 0,
            'title' => $this->optionTitle($path, $context),
            'summary' => $path['summary'],
            'fixes' => [
                'count' => $fixed,
                'of' => $members->count(),
                'all' => $fixed === $members->count(),
                'label' => $fixed === $members->count()
                    ? match ($fixed) { 1 => 'Fixes this problem', 2 => 'Fixes both problems', default => "Fixes all {$fixed} problems" }
                    : "Fixes {$fixed} of {$members->count()} problems",
                'issue_ids' => $path['issue_ids'],
            ],
            'effort' => $this->effort((int) $path['cost']),
            'checked' => $path['verified'] ?? null,
            'apply' => [
                'possible' => !empty($run['request']) && $manual->isEmpty(),
                'manual_steps' => $manual->map(fn($change) => ['step' => $change['step'], 'reason' => $change['reason']])->values()->all(),
            ],
            'steps' => collect($path['steps'])->map(fn($step) => [
                'number' => $step['step'],
                'id' => $step['id'],
                'action' => $step['action'],
                'action_label' => self::ACTION_LABELS[$step['action']] ?? ucfirst($step['action']),
                'text' => $step['label'],
                'fix_in' => $step['result']['fix_in'] ?? null,
                'changes' => $this->changes($step['changes'] ?? [], $context),
            ])->all(),
        ];
    }

    private function optionTitle(array $path, SolutionContext $context): string
    {
        $decision = $path['decision'];
        $target = new Subject($decision['target_id'], $decision['target_type'], Subject::ROLE_REQUEST, (array) ($decision['target_details'] ?? []));
        $name = $this->catalog->for($target->type)->shortName($target, $context);
        $steps = count($path['steps']);

        return match ($path['strategy']) {
            'keep_one' => "Keep {$name} as it is and adjust the rest",
            'clear_obstacles' => $steps === 1 ? ucfirst($path['steps'][0]['label']) : "Keep {$name} and change what is in the way",
            'shared_fix' => $steps === 1 ? ucfirst($path['steps'][0]['label']) : "Fix the shared cause with {$steps} changes",
            default => $steps === 1 ? ucfirst($path['steps'][0]['label']) : "Change {$name}",
        };
    }

    private function changes(array $changes, SolutionContext $context): array
    {
        $byField = collect($changes)->keyBy('field');

        if ($byField->has('start_time') && $byField->has('end_time')) {
            $byField = $byField->except(['start_time', 'end_time'])->put('time', [
                'field' => 'time',
                'from' => Text::time($byField['start_time']['from'], $byField['end_time']['from']),
                'to' => Text::time($byField['start_time']['to'], $byField['end_time']['to']),
            ]);
        }

        return $byField->values()->map(fn($change) => [
            'field' => $change['field'],
            'label' => self::FIELD_LABELS[$change['field']] ?? ucfirst(str_replace('_', ' ', $change['field'])),
            'from' => $this->value($change['field'], $change['from'] ?? null, $context),
            'to' => $this->value($change['field'], $change['to'] ?? null, $context),
        ])->all();
    }

    private function value(string $field, mixed $value, SolutionContext $context): ?string
    {
        return match (true) {
            $value === null => $field === 'hall_id' || $field === 'invigilator_id' ? 'Chosen by the scheduler' : null,
            $field === 'invigilator_id' => $context->invigilatorName($value) ?? $value,
            $field === 'hall_id' => $context->hallName($value) ?? $value,
            $field === 'date', $field === 'excluded_dates' => Text::date($value),
            $field === 'duration' => Text::minutes((int) $value),
            default => (string) $value,
        };
    }

    private function effort(int $cost): array
    {
        return match (true) {
            $cost <= 20 => ['level' => 'small', 'label' => 'Small change'],
            $cost <= 50 => ['level' => 'medium', 'label' => 'Moderate change'],
            default => ['level' => 'large', 'label' => 'Big change'],
        };
    }

    private function summary(array $run): array
    {
        $blocking = $run['issues']->where('severity', 'blocking')->count();
        $warnings = $run['issues']->where('severity', 'warning')->count();
        $unscheduled = count($run['unscheduled']);

        [$headline, $message] = match ($run['status']) {
            ExamTimetableVersion::STATUS_PENDING => ['Not generated yet', 'Generate this version to see its exam timetable and any problems.'],
            ExamTimetableVersion::STATUS_GENERATING => ['Generating the exam timetable', 'This usually takes a few seconds. Problems and solutions will appear here when it finishes.'],
            ExamTimetableVersion::STATUS_OPTIMAL => ['Exam timetable generated with no problems', 'Every course has an exam and every request was respected.'],
            ExamTimetableVersion::STATUS_PARTIAL => ['Exam timetable generated with ' . $warnings . ' request(s) not honoured', 'Every course has an exam. Review the requests below and pick a solution if you want them honoured.'],
            ExamTimetableVersion::STATUS_FAILED => ['Generation could not start', 'Fix the setup problem(s) below, then generate again.'],
            default => ['The exam timetable is incomplete', $blocking . ' problem(s) must be fixed first' . ($unscheduled ? " ({$unscheduled} course(s) have no exam yet)" : '') . '. Each one has suggested solutions.'],
        };

        return [
            'headline' => $headline,
            'message' => $message,
            'blocking_count' => $blocking,
            'warning_count' => $warnings,
            'unscheduled_count' => $unscheduled,
            'dates_affected' => $run['issues']->pluck('date')->filter()->unique()->values()->all(),
        ];
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}
