<?php

namespace App\Jobs\ExamTimetable\Concerns;

use App\Exceptions\AppException;
use App\Models\ExamTimetable\ExamTimetableError;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Job\SystemJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Setup problems found while gathering generation data (no candidates, no
 * exam halls, a joint course without a time...). They are collected, then
 * reported together: the version is marked failed, the errors are stored
 * for the errors drawer and a final "Validation Failed" event is broadcast.
 *
 * Needs BroadcastsExamTimetableGeneration, $currentSchool and $payload.
 */
trait CollectsExamUserErrors
{
    private ?Collection $collectedUserErrors = null;

    protected function userErrors(): Collection
    {
        return $this->collectedUserErrors ??= collect();
    }

    protected function addUserError(string $title, string $description, array $params = []): void
    {
        $this->userErrors()->push([
            'title' => $title,
            'description' => $description,
            'additional_params' => $params,
        ]);
    }

    protected function hasUserErrors(): bool
    {
        return $this->userErrors()->isNotEmpty();
    }

    /**
     * @throws AppException when any user error was collected.
     */
    protected function failOnUserErrors(SystemJob $systemJob, string $versionId, string $examId): void
    {
        if (!$this->hasUserErrors()) {
            return;
        }

        $errors = $this->userErrors()->toArray();
        $errorCount = count($errors);

        ExamTimetableError::create([
            'id' => Str::uuid()->toString(),
            'exam_id' => $examId,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $this->currentSchool->id,
            'status' => 'failed',
            'request_payload' => $this->payload,
            'errors' => $errors,
        ]);

        $version = ExamTimetableVersion::find($versionId);
        $version?->update(['scheduler_status' => ExamTimetableVersion::STATUS_FAILED]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Validation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'details' => "Exam timetable generation failed due to {$errorCount} issue(s)",
            'errors' => $errors,
            'error_count' => $errorCount,
            'version' => $version,
        ]);

        $this->finalEventBroadcasted = true;

        throw new AppException(
            "Validation Process Failed: {$errorCount} error(s) found",
            422,
            'Validation Error',
            'Please fix the following issues'
        );
    }
}
