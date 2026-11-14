<?php

namespace App\Services\ResitTimetable;

use App\Models\ResitTimetable\ResitTimetable;
use App\Models\ResitTimetable\ResitTimetableError;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Services\ExamTimetable\ExamTimetableInsightService;

/** Problems and solutions of a resit timetable version, in the exam timetable's shape. */
class ResitTimetableInsightService extends ExamTimetableInsightService
{
    public function __construct(ConstraintCatalog $catalog, ResitApplySolutionService $solutions)
    {
        parent::__construct($catalog, $solutions);
    }

    protected function findVersion(object $currentSchool, string $versionId): ?object
    {
        return ResitTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);
    }

    protected function latestTimetable(object $currentSchool, string $versionId): ?object
    {
        return ResitTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    protected function latestSetupErrors(object $currentSchool, string $versionId): ?object
    {
        return ResitTimetableError::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    protected function noun(): string
    {
        return 'resit';
    }

    protected function versionFields(): array
    {
        return ['id', 'label', 'version_number', 'scheduler_status', 'resit_exam_id'];
    }
}
