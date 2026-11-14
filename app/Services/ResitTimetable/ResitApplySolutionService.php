<?php

namespace App\Services\ResitTimetable;

use App\Http\Requests\ResitTimetable\GenerateResitTimetableRequest;
use App\Models\ResitTimetable\ResitTimetable;
use App\Models\ResitTimetable\ResitTimetableVersion;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Services\ExamTimetable\ExamApplySolutionService;
use App\Services\ExamTimetable\ExamTimetableGenerationDispatcher;

/** Applies solution paths of a resit timetable version and generates a new version. */
class ResitApplySolutionService extends ExamApplySolutionService
{
    public function __construct(
        ConstraintCatalog $catalog,
        ExamTimetableGenerationDispatcher $examDispatcher,
        private ResitTimetableGenerationDispatcher $resitDispatcher,
    ) {
        parent::__construct($catalog, $examDispatcher);
    }

    protected function dispatchNew(object $currentSchool, array $request, object $user): object
    {
        return $this->resitDispatcher->dispatch($currentSchool, $request, $user);
    }

    protected function latestTimetable(object $currentSchool, string $versionId): ?object
    {
        return ResitTimetable::where('school_branch_id', $currentSchool->id)
            ->where('timetable_version_id', $versionId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    protected function ownerKey(): string
    {
        return 'resit_exam_id';
    }

    protected function requestRules(): array
    {
        return (new GenerateResitTimetableRequest())->rules();
    }

    protected function findVersion(object $currentSchool, string $versionId): ?object
    {
        return ResitTimetableVersion::where('school_branch_id', $currentSchool->id)->find($versionId);
    }

    protected function versionFields(): array
    {
        return ['id', 'label', 'version_number', 'scheduler_status', 'resit_exam_id'];
    }
}
