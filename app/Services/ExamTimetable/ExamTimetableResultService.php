<?php

namespace App\Services\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Builder\ConstraintBuilder;
use App\Models\ExamTimetable\ExamTimetable;
use App\Models\ExamTimetable\ExamTimetableError;
use App\Models\ExamTimetable\ExamTimetableVersion;

/** Reads back what a generation run stored for a version. */
class ExamTimetableResultService
{
    public function getTimetableStatus(string $versionId, object $currentSchool): ?string
    {
        return ExamTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->find($versionId)
            ?->scheduler_status;
    }

    public function getTimetableSlots(string $versionId, object $currentSchool): array
    {
        return $this->toArray($this->latest($versionId, $currentSchool)?->timetable_slots);
    }

    public function getRequestPayload(string $versionId, object $currentSchool): ?array
    {
        return $this->toArray($this->latest($versionId, $currentSchool)?->request_payload);
    }

    /** One row per failed constraint, for the diagnostics list. */
    public function getRawDiagnostics(string $versionId, object $currentSchool): array
    {
        $diagnostics = $this->toArray($this->latest($versionId, $currentSchool)?->raw_diagnostics);
        $result = [];

        foreach (['hard', 'soft'] as $severity) {
            foreach ($diagnostics[$severity] ?? [] as $diagnostic) {
                $failed = $diagnostic['constraint_failed'];
                $result[] = [
                    'severity' => $severity,
                    'constraint_key' => $failed['type'],
                    'constraint_id' => $failed['id'],
                    'constraint_title' => ConstraintBuilder::title($failed['type'], $failed['type']),
                    'constraint_type' => ConstraintBuilder::getConstraintType($failed['type']),
                    ...$failed['details'],
                    'blocker_count' => count($diagnostic['blockers'] ?? []),
                    'blockers' => $diagnostic['blockers'] ?? [],
                ];
            }
        }

        return $result;
    }

    public function getErrors(string $versionId, object $currentSchool): array
    {
        $errors = ExamTimetableError::where('timetable_version_id', $versionId)
            ->where('school_branch_id', $currentSchool->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return $this->toArray($errors?->errors);
    }

    private function latest(string $versionId, object $currentSchool): ?ExamTimetable
    {
        return ExamTimetable::where('timetable_version_id', $versionId)
            ->where('school_branch_id', $currentSchool->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function toArray(mixed $value): array
    {
        return json_decode(json_encode($value ?? []), true) ?? [];
    }
}
