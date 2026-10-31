<?php

namespace App\Jobs\SemesterTimetable\Concerns;

use App\Models\SemesterTimetable\SemesterTimetableVersion;

trait TracksVersionStatus
{
    protected ?string $versionId = null;

    protected function startVersion(string $schoolSemesterId): string
    {
        $version = !empty($this->payload['version_id'])
            ? SemesterTimetableVersion::where('school_branch_id', $this->currentSchool->id)->find($this->payload['version_id'])
            : null;

        $version ??= SemesterTimetableVersion::createNext($this->currentSchool->id, $schoolSemesterId, SemesterTimetableVersion::STATUS_GENERATING);
        $version->update(['scheduler_status' => SemesterTimetableVersion::STATUS_GENERATING]);

        return $this->versionId = $version->id;
    }

    protected function finishVersion(string $status): void
    {
        $versionId = $this->versionId ?? $this->payload['version_id'] ?? null;

        if (!$versionId) {
            return;
        }

        SemesterTimetableVersion::where('school_branch_id', $this->currentSchool->id)
            ->where('id', $versionId)
            ->where('scheduler_status', SemesterTimetableVersion::STATUS_GENERATING)
            ->update(['scheduler_status' => $status]);
    }
}
