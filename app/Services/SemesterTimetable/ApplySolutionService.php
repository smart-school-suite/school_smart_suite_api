<?php

namespace App\Services\SemesterTimetable;

use App\Exceptions\AppException;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\Exceptions\CannotApplyRemedy;
use App\Schedular\SemesterTimetable\Solution\Support\RequestEdit;
use Illuminate\Support\Facades\Validator;

class ApplySolutionService
{
    public function __construct(
        private ConstraintCatalog $catalog,
        private TimetableGenerationDispatcher $dispatcher,
    ) {}

    public function apply(object $currentSchool, string $versionId, array $solutionIds, object $user): array
    {
        $plan = $this->plan($currentSchool, $versionId, $solutionIds);

        if (!$plan['can_apply']) {
            $manual = collect($plan['changes'])->where('applied', false)->pluck('reason')->filter()->values();

            throw new AppException(
                'Solution(s) ' . implode(', ', $solutionIds) . ' cannot be applied automatically.',
                422,
                'Solution Needs Manual Steps',
                $manual->isNotEmpty()
                    ? 'This solution can\'t be applied automatically: ' . $manual->implode(' ')
                    : 'Applying this solution would produce settings that are not valid. ' . collect($plan['validation_errors'])->flatten()->implode(' '),
                null
            );
        }

        $newVersion = $this->dispatcher->dispatch($currentSchool, [...$plan['request'], 'version_id' => null], $user);

        return [
            'source_version' => $this->versionSummary($plan['version']),
            'solutions' => array_map(fn($path) => $this->solutionSummary($path), $plan['paths']),
            'new_version' => $this->versionSummary($newVersion),
            'changes' => $plan['changes'],
            'message' => "{$newVersion->label} is being generated with the chosen " . (count($plan['paths']) === 1 ? 'solution' : 'solutions') . " applied. {$plan['version']->label} stays as it is.",
        ];
    }

    public function assess(array $request, array $path, RequestEdit $edit): array
    {
        $changes = [];

        foreach ($path['steps'] ?? [] as $step) {
            try {
                $request = $this->catalog->for($step['target']['type'])->applyToRequest($request, $step, $edit);
                $changes[] = ['step' => $step['step'], 'text' => $step['label'], 'applied' => true, 'reason' => null];
            } catch (CannotApplyRemedy $exception) {
                $changes[] = ['step' => $step['step'], 'text' => $step['label'], 'applied' => false, 'reason' => $exception->getMessage()];
            }
        }

        return [$request, $changes];
    }

    public function editFor(array $rawDiagnostics): RequestEdit
    {
        $subjects = [];

        foreach ([Issue::HARD, Issue::SOFT] as $severity) {
            foreach ($rawDiagnostics[$severity] ?? [] as $diagnostic) {
                $issue = Issue::fromDiagnostic($diagnostic, $severity);

                if (!$issue) {
                    continue;
                }

                $subjects[$issue->id()] = $issue->subject;
                foreach ($issue->obstacles as $obstacle) {
                    $subjects[$obstacle->id] ??= $obstacle;
                }
            }
        }

        return new RequestEdit($this->catalog, $subjects);
    }

    private function plan(object $currentSchool, string $versionId, array $solutionIds): array
    {
        $version = SemesterTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);

        if (!$version) {
            throw new AppException(
                "Timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Timetable Version Not Found',
                "We couldn't find this timetable version. It may have been deleted.",
                null
            );
        }

        $timetable = SemesterTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();

        $suggestions = collect($this->asArray($timetable?->raw_suggestions ?? []))->flatten(1)->filter(fn($path) => is_array($path) && isset($path['id']));
        $solutionIds = array_values(array_unique($solutionIds));
        $paths = array_map(fn($id) => $suggestions->firstWhere('id', $id), $solutionIds);
        $missing = array_values(array_diff($solutionIds, array_column(array_filter($paths), 'id')));

        if (empty($solutionIds) || $missing) {
            throw new AppException(
                'Solution(s) ' . implode(', ', $missing) . " not found on timetable version {$versionId}.",
                404,
                'Solution Not Found',
                'This solution is not available on this version any more. Open the latest solutions for the version and pick one again.',
                null
            );
        }

        $sharedIssues = collect($paths)->flatMap(fn($path) => $path['issue_ids'])->duplicates()->values();

        if ($sharedIssues->isNotEmpty()) {
            throw new AppException(
                'Selected solutions overlap on issue(s) ' . $sharedIssues->implode(', ') . '.',
                422,
                'Solutions Overlap',
                'Two of the chosen solutions fix the same problem. Pick only one solution for each problem.',
                null
            );
        }

        $request = $this->asArray($timetable->request_payload ?? []);
        unset($request['version_id']);

        if (empty($request['school_semester_id'])) {
            throw new AppException(
                "Timetable version {$versionId} has no stored generation settings.",
                422,
                'Settings Not Available',
                'The settings used to generate this version were not saved, so a solution can\'t be applied to them. Generate the version again, then apply a solution.',
                null
            );
        }

        $edit = $this->editFor($this->asArray($timetable->raw_diagnostics ?? []));
        $adjusted = $request;
        $changes = [];
        foreach ($paths as $path) {
            [$adjusted, $pathChanges] = $this->assess($adjusted, $path, $edit);
            array_push($changes, ...array_map(fn($change) => [...$change, 'solution_id' => $path['id']], $pathChanges));
        }

        $validator = Validator::make($adjusted, collect((new GenerateSemesterTimetableRequest())->rules())->except('version_id')->all());
        $errors = $validator->fails() ? $validator->errors()->toArray() : [];
        $applied = collect($changes)->every(fn($change) => $change['applied']);

        return [
            'version' => $version,
            'paths' => $paths,
            'request' => $adjusted,
            'changes' => $changes,
            'validation_errors' => $errors,
            'can_apply' => $applied && empty($errors),
        ];
    }

    private function solutionSummary(array $path): array
    {
        return [
            'id' => $path['id'],
            'strategy' => $path['strategy'],
            'summary' => $path['summary'],
            'issue_ids' => $path['issue_ids'],
        ];
    }

    private function versionSummary(SemesterTimetableVersion $version): array
    {
        return $version->only(['id', 'label', 'version_number', 'scheduler_status']);
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}
