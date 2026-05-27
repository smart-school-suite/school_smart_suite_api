<?php

namespace App\Services\ExamTimetable;

use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Exceptions\AppException;

class ExamTimetableVersionService
{
    public function getVersionExamId(object $currentSchool, string $examId)
    {
        $versions = ExamTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->orderBy('created_at', 'desc')
            ->get();

        $latestId = $versions->first()?->id;

        return $versions->map(function ($version) use ($latestId) {
            return [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'label' => $version->label,
                'scheduler_status' => $version->scheduler_status,
                'is_latest' => $version->id === $latestId,
                'created_at' => $version->created_at,
                'updated_at' => $version->updated_at,
            ];
        });
    }
    public function createVersion(object $currentSchool, array $data)
    {
        $latest = ExamTimetableVersion::where([
            'school_branch_id'   => $currentSchool->id,
            'exam_id' => $data['exam_id'],
        ])
            ->max('version_number');

        $nextNumber = ($latest ?? 0) + 1;

        return ExamTimetableVersion::create([
            'version_number'     => $nextNumber,
            'label'              => "Version $nextNumber",
            'school_branch_id'   => $currentSchool->id,
            'exam_id' => $data['exam_id'],
        ]);
    }
    public function deleteVersion(object $currentSchool, string $versionId)
    {
        $version = ExamTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("id", $versionId)
            ->first();
        if (!$version) {
            throw new AppException(
                "Timetable Version Not Found",
                404,
                "Not Found",
                "The Timetable Version Your Trying to delete was not found please ensure that it has not been deleted and try again"
            );
        }

        $version->delete();
        return $version;
    }
}
