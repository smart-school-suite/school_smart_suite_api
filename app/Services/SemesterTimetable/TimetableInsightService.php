<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableError;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\Teacher;
use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

class TimetableInsightService
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
        'day' => 'Day',
        'time' => 'Time',
        'start_time' => 'Start time',
        'end_time' => 'End time',
        'teacher_id' => 'Teacher',
        'hall_id' => 'Hall',
        'duration_minutes' => 'Lesson length',
        'max' => 'Maximum',
        'no_break_exceptions' => 'Break',
        'break' => 'Break',
        'allow_outside_preference' => 'Outside preferred hours',
    ];

    public function __construct(private ConstraintCatalog $catalog) {}

    public function issues(object $currentSchool, string $versionId): array
    {
        $run = $this->load($currentSchool, $versionId);

        return [
            'version' => $run['version'],
            'summary' => $this->summary($run),
            'issues' => $run['issues']->values()->all(),
        ];
    }

    public function solutions(object $currentSchool, string $versionId, ?string $issueId = null): array
    {
        $run = $this->load($currentSchool, $versionId);

        if ($issueId && !$run['issues']->contains('id', $issueId)) {
            throw new AppException(
                "Issue {$issueId} was not found on timetable version {$versionId}.",
                404,
                "Issue Not Found",
                "This problem no longer exists on this timetable version. It may have been fixed by a newer generation.",
                null
            );
        }

        $problems = $this->problems($run)
            ->when($issueId, fn(Collection $problems) => $problems
                ->filter(fn(array $problem) => collect($problem['issues'])->contains('id', $issueId))
                ->map(fn(array $problem) => $this->onlyOptionsFixing($problem, $issueId)))
            ->values()
            ->all();

        return [
            'version' => $run['version'],
            'summary' => $this->summary($run),
            'problems' => $problems,
        ];
    }

    private function load(object $currentSchool, string $versionId): array
    {
        $version = SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);

        if (!$version) {
            throw new AppException(
                "Timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                "Timetable Version Not Found",
                "We couldn't find this timetable version. It may have been deleted.",
                null
            );
        }

        $timetable = SemesterTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();

        $setupErrors = $version->scheduler_status === SemesterTimetableVersion::STATUS_FAILED
            ? SemesterTimetableError::where('school_branch_id', $currentSchool->id)
                ->where('timetable_version_id', $versionId)
                ->orderBy('created_at', 'desc')
                ->first()
            : null;

        $diagnostics = [
            Issue::HARD => $this->asArray($timetable?->raw_diagnostics['hard'] ?? []),
            Issue::SOFT => $this->asArray($timetable?->raw_diagnostics['soft'] ?? []),
        ];
        $suggestions = $this->asArray($timetable?->raw_suggestions ?? []);
        $context = new SolutionContext($this->schedulerInput($timetable, $diagnostics), [], false);

        $paths = collect($suggestions)->flatten(1)->filter(fn($path) => is_array($path) && isset($path['issue_ids']))->values();
        $issues = $this->issueList($diagnostics, $context, $paths)
            ->concat($this->setupIssues($setupErrors));

        return [
            'version' => [
                'id' => $version->id,
                'label' => $version->label,
                'version_number' => $version->version_number,
                'scheduler_status' => $version->scheduler_status,
            ],
            'status' => $version->scheduler_status,
            'issues' => $issues,
            'paths' => $paths,
            'context' => $context,
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
            ->sortBy(fn(array $issue) => [$this->dayOrder($issue['day']), $issue['time'] ?? ''])
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
            'severity_label' => $blocking ? 'Must be fixed before the timetable can be generated' : 'This request could not be honoured',
            'day' => $issue->day(),
            'day_label' => Text::day($issue->day()),
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
            'severity_label' => 'Must be fixed before the timetable can be generated',
            'day' => null,
            'day_label' => null,
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
            $subject->get('teacher_id') ? ['type' => 'teacher', 'id' => $subject->get('teacher_id'), 'name' => Text::teacher($subject, $context)] : null,
            $subject->get('hall_id') ? ['type' => 'hall', 'id' => $subject->get('hall_id'), 'name' => Text::hall($subject, $context)] : null,
        ]));
    }

    private function problems(array $run): Collection
    {
        $issues = $run['issues']->keyBy('id');
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
                    ->map(fn($path, $index) => $this->presentOption($path, $index, $members, $run['context']))
                    ->all();
                $first = $members->first();

                return [
                    'id' => md5($ids->sort()->implode(',')),
                    'day' => $first['day'],
                    'day_label' => $first['day_label'],
                    'title' => $members->count() === 1
                        ? $first['title']
                        : $members->count() . ' connected problems on ' . ($first['day_label'] ?? 'this timetable') . $this->around($members),
                    'description' => $members->count() === 1
                        ? implode('. ', $first['reasons'])
                        : 'These problems affect each other, so they are best fixed together.',
                    'issues' => $members->map(fn($issue) => ['id' => $issue['id'], 'title' => $issue['title'], 'severity' => $issue['severity']])->all(),
                    'option_count' => count($options),
                    'options' => $options,
                ];
            })
            ->sortBy(fn(array $problem) => $this->dayOrder($problem['day']))
            ->values();
    }

    private function presentOption(array $path, int $index, Collection $members, SolutionContext $context): array
    {
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
            'steps' => collect($path['steps'])->map(fn($step) => [
                'number' => $step['step'],
                'id' => $step['id'],
                'action' => $step['action'],
                'action_label' => self::ACTION_LABELS[$step['action']] ?? ucfirst($step['action']),
                'text' => $step['label'],
                'changes' => $this->changes($step['changes'] ?? [], $context),
            ])->all(),
        ];
    }

    private function around(Collection $members): string
    {
        $times = $members->pluck('time')->filter()->map(fn($time) => explode(' – ', $time));

        if ($times->isEmpty()) {
            return '';
        }

        return ' around ' . Text::time($times->min(fn($time) => $time[0]), $times->max(fn($time) => $time[1]));
    }

    private function optionTitle(array $path, SolutionContext $context): string
    {
        $decision = $path['decision'];
        $target = new Subject($decision['target_id'], $decision['target_type'], Subject::ROLE_REQUEST, (array) ($decision['target_details'] ?? []));
        $name = $this->catalog->for($target->type)->shortName($target, $context);
        $steps = count($path['steps']);

        return match ($path['strategy']) {
            'keep_one' => "Keep {$name} as it is and adjust the rest",
            'clear_obstacles' => "Keep {$name} and change what is in the way",
            'shared_fix' => 'Fix the shared cause' . ($steps === 1 ? ' with one change' : " with {$steps} changes"),
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
            $value === null => null,
            $field === 'teacher_id' => $context->teacherName($value) ?? $value,
            $field === 'hall_id' => $context->hallName($value) ?? $value,
            $field === 'day' => Text::day($value),
            $field === 'duration_minutes' => "{$value} min",
            $field === 'no_break_exceptions' => 'No break on ' . Text::day($value),
            is_bool($value) => $value ? 'Allowed' : 'Not allowed',
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
        $days = $run['issues']->pluck('day_label')->filter()->unique()->values()->all();

        [$headline, $message] = match ($run['status']) {
            SemesterTimetableVersion::STATUS_PENDING => ['Not generated yet', 'Generate this version to see its timetable and any problems.'],
            SemesterTimetableVersion::STATUS_GENERATING => ['Generating the timetable', 'This usually takes a few seconds. Problems and solutions will appear here when it finishes.'],
            SemesterTimetableVersion::STATUS_OPTIMAL => ['Timetable generated with no problems', 'Every rule and request was respected.'],
            SemesterTimetableVersion::STATUS_PARTIAL => ['Timetable generated with ' . $warnings . ' request(s) not honoured', 'The timetable is usable. Review the requests below and pick a solution if you want them honoured.'],
            SemesterTimetableVersion::STATUS_FAILED => ['Generation could not start', 'Fix the setup problem(s) below, then generate again.'],
            default => ['The timetable could not be generated', $blocking . ' problem(s) must be fixed first. Each one has suggested solutions.'],
        };

        return [
            'headline' => $headline,
            'message' => $message,
            'blocking_count' => $blocking,
            'warning_count' => $warnings,
            'days_affected' => $days,
        ];
    }

    private function onlyOptionsFixing(array $problem, string $issueId): array
    {
        $options = collect($problem['options'])
            ->filter(fn($option) => in_array($issueId, $option['fixes']['issue_ids'], true))
            ->values()
            ->map(fn($option, $index) => [...$option, 'rank' => $index + 1, 'recommended' => $index === 0])
            ->all();

        return [...$problem, 'option_count' => count($options), 'options' => $options];
    }

    private function schedulerInput(?SemesterTimetable $timetable, array $diagnostics): array
    {
        $input = $this->asArray($timetable?->scheduler_input ?? []);

        if (!empty($input['teachers']) || !empty($input['halls'])) {
            return $input;
        }

        $request = $this->asArray($timetable?->request_payload ?? []);
        $ids = collect($diagnostics)->flatten(1)->map(fn($diagnostic) => $this->asArray($diagnostic))
            ->flatMap(fn($diagnostic) => [
                $diagnostic['constraint_failed']['details'] ?? [],
                ...collect($diagnostic['blockers'] ?? [])->map(fn($blocker) => [...($this->asArray($blocker)['entity'] ?? []), ...($this->asArray($blocker)['conflict'] ?? [])])->all(),
            ]);

        $teacherIds = $ids->pluck('teacher_id')->filter()->unique()->all();
        $hallIds = $ids->pluck('hall_id')->filter()->unique()->all();
        $courseIds = $ids->pluck('course_id')->filter()->unique()->all();

        return [
            'teachers' => Teacher::whereIn('id', $teacherIds)->get()->map(fn($teacher) => ['teacher_id' => $teacher->id, 'name' => $teacher->name])->all(),
            'halls' => Hall::whereIn('id', $hallIds)->get()->map(fn($hall) => ['hall_id' => $hall->id, 'hall_name' => $hall->name])->all(),
            'teacher_courses' => Courses::whereIn('id', $courseIds)->get()->map(fn($course) => ['course_id' => $course->id, 'course_title' => $course->course_title])->all(),
            'hard_constraints' => array_filter([
                'operational_period' => $request['operational_period'] ?? null,
                'schedule_period_duration_minutes' => $request['schedule_period_duration_minutes'] ?? null,
                'break_period' => $request['break_period'] ?? null,
            ]),
        ];
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }

    private function dayOrder(?string $day): int
    {
        return array_search($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], true) ?: ($day === 'monday' ? 0 : 9);
    }
}
