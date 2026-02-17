<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetableDraft\CreateSemesterTimetableDraftRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SemesterTimetable\SemesterTimetableDraftService;

class SemesterTimetableDraftController extends Controller
{
    protected SemesterTimetableDraftService $timetableDraftService;

    public function __construct(SemesterTimetableDraftService $timetableDraftService)
    {
        $this->timetableDraftService = $timetableDraftService;
    }

    public function createTimetableDraft(CreateSemesterTimetableDraftRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createDraft = $this->timetableDraftService->createTimetableDraft($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable Draft Created Successfully", $createDraft, null, 201);
    }

    public function getTimetableDrafts(Request $request, string $schoolSemesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $drafts = $this->timetableDraftService->getTimetableDrafts($schoolSemesterId, $currentSchool);
        return ApiResponseService::success("Timetable Drafts fetched Successfully", $drafts, null, 200);
    }

    public function deleteTimetableDraft(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolSemesterId = $request->route('schoolSemesterId');
        $draftId = $request->route('draftId');
        $this->timetableDraftService->deleteTimetableDraft($draftId, $schoolSemesterId, $currentSchool);
        return ApiResponseService::success("Timetable Draft Deleted Successfully", null, null, 200);
    }
    public function getTimetableDraftWithVersions(Request $request, string $schoolSemesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableDraftVersions = $this->timetableDraftService->getTimetableDraftVersions($schoolSemesterId, $currentSchool);
        return ApiResponseService::success("Timetable Draft Versions fetched Successfully", $timetableDraftVersions, null, 200);
    }
}
