<?php

namespace App\Http\Controllers\PeriodDuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodDuration\CreatePeriodDurationRequest;
use App\Http\Requests\PeriodDuration\UpdatePeriodDurationRequest;
use App\Services\ApiResponseService;
use App\Services\PeriodDuration\PeriodDurationService;
use Illuminate\Http\Request;

class PeriodDurationController extends Controller
{
    protected PeriodDurationService $periodDurationService;
    public function __construct(PeriodDurationService $periodDurationService)
    {
        $this->periodDurationService = $periodDurationService;
    }

    public function createPeriodDuration(CreatePeriodDurationRequest $request)
    {
        $createPeriodDuration =  $this->periodDurationService->createDuration($request->validated());
        return ApiResponseService::success("Period Duration Created Successfully", $createPeriodDuration, null, 201);
    }

    public function updatePeriodDuration(UpdatePeriodDurationRequest $request, string $periodDurationId)
    {
        $updatePeriodDuration = $this->periodDurationService->updateDuration($periodDurationId, $request->validated());
        return ApiResponseService::success("Period Duration Updated Successfully", $updatePeriodDuration, null, 200);
    }

    public function activatePeriodDuration(Request $request, string $periodDurationId)
    {
        $activatePeriodDuration = $this->periodDurationService->activateDuration($periodDurationId);
        return ApiResponseService::success("Period Duration Activated Successfully", $activatePeriodDuration, null, 200);
    }

    public function deactivatePeriodDuration(Request $request, string $periodDurationId)
    {
        $deactivatePeriodDuration = $this->periodDurationService->deactivateDuration($periodDurationId);
        return ApiResponseService::success("Period Duration Deactivated Successfully", $deactivatePeriodDuration, null, 200);
    }

    public function getExamTimetablePeriodDuration(Request $request)
    {
        $periodDuration = $this->periodDurationService->getExamTimetableDurations();
        return ApiResponseService::success("Exam Timetable Period Duration Fetched Successfully", $periodDuration, null, 200);
    }

    public function getSemesterTimetablePeriodDuration(Request $request)
    {
        $periodDuration = $this->periodDurationService->getSemesterTimetableDuration();
        return ApiResponseService::success("Semester Timetable Period Duration Fetched Successfully", $periodDuration, null, 200);
    }

    public function getResitTimetablePeriodDuration(Request $request)
    {
        $periodDuration = $this->periodDurationService->getResitTimetableDuration();
        return ApiResponseService::success("Resit Exam Timetable Period Duration Fetched Successfully", $periodDuration, null, 200);
    }

    public function deletePeriodDuration(Request $request, string $periodDurationId)
    {
        $deletePeriodDuration = $this->periodDurationService->deleteDuration($periodDurationId);
        return ApiResponseService::success("Period Duration Deleted Successfully", $deletePeriodDuration, null, 200);
    }

    public function getAllPeriodDuration(Request $request)
    {
        $periodDurations = $this->periodDurationService->getAllTimetableDurations();
        return ApiResponseService::success("Period Duration Fetched Successfully", $periodDurations, null, 200);
    }

    public function getPeriodDurationById(Request $request, string $periodDurationId)
    {
        $periodDuration = $this->periodDurationService->getDurationById($periodDurationId);
        return ApiResponseService::success("Period Duration Fetched Successfully", $periodDuration, null, 200);
    }
}
