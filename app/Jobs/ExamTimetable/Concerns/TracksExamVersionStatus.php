<?php

namespace App\Jobs\ExamTimetable\Concerns;

use App\Models\ExamTimetable\ExamTimetableVersion;

trait TracksExamVersionStatus
{
    protected ?string $versionId = null;

    protected function startVersion(string $examId): string
    {
        $version = !empty($this->payload['version_id'])
            ? ExamTimetableVersion::where('school_branch_id', $this->currentSchool->id)->find($this->payload['version_id'])
            : null;

        $version ??= ExamTimetableVersion::createNext($this->currentSchool->id, $examId, ExamTimetableVersion::STATUS_GENERATING);
        $version->update(['scheduler_status' => ExamTimetableVersion::STATUS_GENERATING]);

        return $this->versionId = $version->id;
    }

    protected function finishVersion(string $status): void
    {
        $versionId = $this->versionId ?? $this->payload['version_id'] ?? null;

        if (!$versionId) {
            return;
        }

        ExamTimetableVersion::where('school_branch_id', $this->currentSchool->id)
            ->where('id', $versionId)
            ->where('scheduler_status', ExamTimetableVersion::STATUS_GENERATING)
            ->update(['scheduler_status' => $status]);
    }
}
