<?php

namespace App\Http\Controllers\Hall;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\UpdateHallRequest;
use App\Http\Requests\HallType\CreateHallTypeRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Hall\HallTypeService;

class HallTypeController extends Controller
{
    protected HallTypeService $hallTypeService;
    public function __construct(HallTypeService $hallTypeService)
    {
        $this->hallTypeService = $hallTypeService;
    }
    public function createHallType(CreateHallTypeRequest $request)
    {
        $createHallType = $this->hallTypeService->createHallType($request->validated());
        return ApiResponseService::success("Hall Created Successfully", $createHallType, null, 201);
    }

    public function updateHallType(UpdateHallRequest $request, $hallTypeId)
    {
        $updateHallType = $this->hallTypeService->updateHallType($request->validated(), $hallTypeId);
        return ApiResponseService::success("Hall Type Updated Successfully", $updateHallType, null, 200);
    }

    public function getActiveHallTypes(Request $request)
    {
        $activeHallTypes = $this->hallTypeService->getActiveHallTypes();
        return ApiResponseService::success("Active Hall Types Fetched Successfully", $activeHallTypes, null, 200);
    }

    public function getAllHallTypes(Request $request)
    {
        $hallTypes = $this->hallTypeService->getAllHallTypes();
        return ApiResponseService::success("Hall Types Fetched Successfully", $hallTypes, null, 200);
    }

    public function deactivateHallType(Request $request, $hallTypeId)
    {
        $deactivateHallType = $this->hallTypeService->deactivateHallType($hallTypeId);
        return ApiResponseService::success("Hall Type Deactivated Successfully", $deactivateHallType, null, 200);
    }

    public function activateHallType(Request $request, $hallTypeId)
    {
        $activateHallType = $this->hallTypeService->activateHallType($hallTypeId);
        return ApiResponseService::success("Hall Type Activated Successfully",  $activateHallType, null, 200);
    }

    public function deleteHallType(Request $request, $hallTypeId)
    {
        $deleteHallType = $this->hallTypeService->deleteHallType($hallTypeId);
        return ApiResponseService::success("Hall Type Deleted Successfully", $deleteHallType, null, 200);
    }
}
