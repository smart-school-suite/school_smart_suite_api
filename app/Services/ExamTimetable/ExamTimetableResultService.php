<?php

namespace App\Services\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Builder\ConstraintBuilder;
use App\Exceptions\AppException;
use App\Models\ExamTimetable\ExamTimetableError;
use App\Models\ExamTimetable\ExamTimetableRun;
use App\Models\ExamTimetable\ExamTimetableVersion;

/** Stored results of an exam timetable generation, one version at a time (like SemesterTimetableService). */
class ExamTimetableResultService
{
    public function __construct(private ExamTimetableInsightService $insights) {}

    public function getTimetableStatus(string $versionId, object $currentSchool): array
    {
        $version = $this->version($versionId, $currentSchool);
        $run = $this->run($versionId, $currentSchool);

        return [
            'version_id' => $version->id,
            'scheduler_status' => $version->scheduler_status,
            'status' => $run?->status,
            'unscheduled_course_count' => count($run?->unscheduled_courses ?? []),
        ];
    }

    public function getTimetableSlots(string $versionId, object $currentSchool): array
    {
        $run = $this->run($versionId, $currentSchool);

        return [
            'dates' => $this->asArray($run?->timetable_slots ?? []),
            'unscheduled_courses' => $this->asArray($run?->unscheduled_courses ?? []),
        ];
    }

    public function getRequestPayload(string $versionId, object $currentSchool): ?array
    {
        return $this->asArray($this->run($versionId, $currentSchool)?->request_payload
            ?? $this->error($versionId, $currentSchool)?->request_payload
            ?? []);
    }

    public function getParsedDiagnostics(string $versionId, object $currentSchool): array
    {
        return $this->insights->issues($currentSchool, $versionId);
    }

    public function getRawDiagnostics(string $versionId, object $currentSchool): array
    {
        $run = $this->run($versionId, $currentSchool);
        $raw = $this->asArray($run?->raw_diagnostics ?? []);
        $result = [];

        foreach (['hard', 'soft'] as $severity) {
            foreach ($raw[$severity] ?? [] as $diagnostic) {
                $type = $diagnostic['constraint_failed']['type'] ?? null;

                $result[] = [
                    'severity' => $severity,
                    'constraint_key' => $type,
                    'constraint_id' => $diagnostic['constraint_failed']['id'] ?? null,
                    'constraint_title' => $type ? ConstraintBuilder::getConstraintTitle($type) : null,
                    'constraint_type' => $type ? ConstraintBuilder::getConstraintType($type) : null,
                    ...($diagnostic['constraint_failed']['details'] ?? []),
                    'blocker_count' => count($diagnostic['blockers'] ?? []),
                ];
            }
        }

        return $result;
    }

    public function getErrors(string $versionId, object $currentSchool): array
    {
        $this->version($versionId, $currentSchool);

        return $this->asArray($this->error($versionId, $currentSchool)?->errors ?? []);
    }

    private function version(string $versionId, object $currentSchool): ExamTimetableVersion
    {
        return ExamTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId)
            ?? throw new AppException(
                "Exam timetable version {$versionId} not found for school branch {$currentSchool->id}.",
                404,
                'Exam Timetable Version Not Found',
                "We couldn't find this exam timetable version. It may have been deleted.",
                null
            );
    }

    private function run(string $versionId, object $currentSchool): ?ExamTimetableRun
    {
        return ExamTimetableRun::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function error(string $versionId, object $currentSchool): ?ExamTimetableError
    {
        return ExamTimetableError::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function asArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?? [];
    }
}
