<?php

namespace App\Services\ResitTimetable;

use App\Exceptions\AppException;
use App\Models\ResitTimetable\ResitTimetableVersion;

class ResitTimetableVersionService
{
    public function versionsFor(object $currentSchool, string $resitExamId)
    {
        $versions = ResitTimetableVersion::where('school_branch_id', $currentSchool->id)
            ->where('resit_exam_id', $resitExamId)
            ->withCount('slots')
            ->orderBy('created_at', 'desc')
            ->get();

        $latestId = $versions->first()?->id;

        return $versions->map(fn(ResitTimetableVersion $version) => [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'label' => $version->label,
            'scheduler_status' => $version->scheduler_status,
            'is_latest' => $version->id === $latestId,
            'is_active' => $version->slots_count > 0,
            'created_at' => $version->created_at,
            'updated_at' => $version->updated_at,
        ])->values();
    }

    public function createVersion(object $currentSchool, array $data): ResitTimetableVersion
    {
        return ResitTimetableVersion::createNext($currentSchool->id, $data['resit_exam_id']);
    }

    public function deleteVersion(object $currentSchool, string $versionId): ResitTimetableVersion
    {
        $version = ResitTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId)
            ?? throw new AppException(
                'Timetable Version Not Found',
                404,
                'Not Found',
                'The resit timetable version you are trying to delete was not found. It may already have been deleted.'
            );

        if ($version->slots()->exists()) {
            throw new AppException(
                "Resit timetable version {$versionId} is active and cannot be deleted.",
                422,
                'Version In Use',
                'This version is the active resit timetable. Activate another version first, then delete this one.'
            );
        }

        $version->delete();
        return $version;
    }
}
