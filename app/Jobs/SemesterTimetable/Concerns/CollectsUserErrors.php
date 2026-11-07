<?php

namespace App\Jobs\SemesterTimetable\Concerns;

use App\Exceptions\AppException;
use App\Models\Job\SystemJob;
use App\Models\SemesterTimetable\SemesterTimetableError;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Setup problems found while gathering generation data (for example a joint
 * course with no time set). They are collected, then reported together: the
 * version is marked failed, the errors are stored for the errors drawer, and a
 * final "Validation Failed" event is broadcast.
 *
 * Needs BroadcastsTimetableGeneration, $currentSchool and $payload.
 */
trait CollectsUserErrors
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

    /** Stored in Mongo; the errors drawer reads them back for a failed version. */
    protected function storeUserErrors(string $versionId, string $schoolSemesterId, array $errors): void
    {
        SemesterTimetableError::create([
            'id' => Str::uuid()->toString(),
            'school_semester_id' => $schoolSemesterId,
            'timetable_version_id' => $versionId,
            'school_branch_id' => $this->currentSchool->id,
            'status' => 'failed',
            'request_payload' => $this->payload,
            'errors' => $errors,
        ]);
    }

    /**
     * @throws AppException when any user error was collected.
     */
    protected function failOnUserErrors(SystemJob $systemJob, string $versionId, string $schoolSemesterId): void
    {
        if (!$this->hasUserErrors()) {
            return;
        }

        $errors = $this->userErrors()->toArray();
        $errorCount = count($errors);

        $this->storeUserErrors($versionId, $schoolSemesterId, $errors);

        $version = SemesterTimetableVersion::find($versionId);
        $version?->update(['scheduler_status' => SemesterTimetableVersion::STATUS_FAILED]);

        $this->broadcastGeneration($systemJob, [
            'stage' => 'Validation Failed',
            'percentage' => 100,
            'status' => 'failed',
            'details' => "Timetable generation failed due to {$errorCount} issue(s)",
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
