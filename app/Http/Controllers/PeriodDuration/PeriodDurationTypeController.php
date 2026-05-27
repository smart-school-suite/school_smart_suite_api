<?php

namespace App\Http\Controllers\PeriodDuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodDurationType\CreatePeriodDurationTypeRequest;
use App\Http\Requests\PeriodDurationType\UpdatePeriodDurationtypeRequest;
use App\Services\ApiResponseService;
use App\Services\PeriodDuration\PeriodDurationTypeService;
use Illuminate\Http\Request;

class PeriodDurationTypeController extends Controller
{
    protected PeriodDurationTypeService $periodDurationTypeService;
    public function __construct(PeriodDurationTypeService $periodDurationTypeService)
    {
        $this->periodDurationTypeService = $periodDurationTypeService;
    }

    public function createPeriodDurationType(CreatePeriodDurationTypeRequest $request)
    {
        $periodDurationType = $this->periodDurationTypeService->createDurationType($request->validated());
        return ApiResponseService::success("Period Duration Type Fetched Successfully", $periodDurationType, null, 200);
    }

    public function updateDurationType(UpdatePeriodDurationtypeRequest $request, string $typeId)
    {
        $updatePeriodDuration = $this->periodDurationTypeService->updateDurationType($typeId, $request->validated());
        return ApiResponseService::success("Period Duration Type Updated Successfully", $updatePeriodDuration, null, 200);
    }

    public function getPeriodDurationTypes(Request $request)
    {
        $periodDurationTypes = $this->periodDurationTypeService->getDurationTypes();
        return ApiResponseService::success("Period Duration Types Fetched Successfully", $periodDurationTypes, null, 200);
    }

    public function getPeriodDurationTypeById(Request $request, string $typeId)
    {
        $periodDurationType = $this->periodDurationTypeService->getDurationTypeById($typeId);
        return ApiResponseService::success("Period Duration Type Fetched Successfully", $periodDurationType, null, 200);
    }

    public function deletePeriodDurationType(Request $request, string $typeId)
    {
        $deleteDurationType = $this->periodDurationTypeService->deleteDurationType($typeId);
        return ApiResponseService::success("Period Duration Deleted Successfully", $deleteDurationType, null, 200);
    }

    public function activatePeriodDuration(Request $request, string $typeId)
    {
        $activatePeriodDuration = $this->periodDurationTypeService->activateDurationType($typeId);
        return ApiResponseService::success("Period Duration Activated Successfully", $activatePeriodDuration, null, 200);
    }

    public function deactivatePeriodDuration(Request $request, string $typeId)
    {
        $deactivatePeriodDuration = $this->periodDurationTypeService->deactivateDurationType($typeId);
        return ApiResponseService::success("Period Duration Deactivated Successfully", $deactivatePeriodDuration, null, 200);
    }
}
