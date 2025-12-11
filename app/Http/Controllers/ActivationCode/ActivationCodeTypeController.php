<?php

namespace App\Http\Controllers\ActivationCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivationCodeType\CreateActivationCodeTypeRequest;
use App\Http\Requests\ActivationCodeType\UpdateActivationCodeTypeRequest;
use Illuminate\Http\Request;
use App\Services\ActivationCode\ActivationCodeTypeService;
use App\Services\ApiResponseService;

class ActivationCodeTypeController extends Controller
{
    protected ActivationCodeTypeService $activationCodeTypeService;
    public function __construct(ActivationCodeTypeService $activationCodeTypeService)
    {
        $this->activationCodeTypeService = $activationCodeTypeService;
    }
    public function createActivationCodeType(CreateActivationCodeTypeRequest $request)
    {
        $createActivationCodeType = $this->activationCodeTypeService->createActivationCodeType($request->validated());
        return ApiResponseService::success("Activation Code Type Created Successfully", $createActivationCodeType, null, 201);
    }
    public function updateActivationCodeType(UpdateActivationCodeTypeRequest $request, $activationCodeTypeId)
    {
        $updateActivationCodeType = $this->activationCodeTypeService->updateActivationCodeType($request->validated(), $activationCodeTypeId);
        return ApiResponseService::success("Activation Code Type updated Successfully", $updateActivationCodeType, null, 200);
    }
    public function getActivationCodeType(Request $request)
    {
        $activationCodeTypes = $this->activationCodeTypeService->getAllActivationCodeTypes();
        return ApiResponseService::success("Activation Code Types Fetched Successfully", $activationCodeTypes, null, 200);
    }
    public function getActivationCodeTypeDetail(Request $request, $activationCodeTypeId)
    {
        $activationCodeType = $this->activationCodeTypeService->getActivationCodeTypeDetail($activationCodeTypeId);
        return ApiResponseService::success("Activation Code Type Details Fetched Successfully", $activationCodeType, null, 200);
    }
    public function deleteActivationCodeType(Request $request, $activationCodeTypeId)
    {
        $deleteActivationCodeType = $this->activationCodeTypeService->deleteActivationCodeType($activationCodeTypeId);
        return ApiResponseService::success("Activation Code Type Deleted Successfully", $deleteActivationCodeType, null, 200);
    }
    public function activationActivationCodeType(Request $request, $activationCodeTypeId)
    {
        $activateCodeType = $this->activationCodeTypeService->activationActivationCodeType($activationCodeTypeId);
        return ApiResponseService::success("Activation Code Type Activated Successfully", $activateCodeType, null, 200);
    }
    public function deactivateActivationCodeType(Request $request, $activationCodeTypeId)
    {
        $deactivateCodeType = $this->activationCodeTypeService->deactivateActivationCodeType($activationCodeTypeId);
        return ApiResponseService::success("Activation Code Type Deactivated Successfully", $deactivateCodeType, null, 200);
    }
    public function getActivationCodeTypeCountryId(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $activationCodeType = $this->activationCodeTypeService->getActivationCodeTypeCountryId($currentSchool);
        return ApiResponseService::success("Activation Code Type Fetched Successfully", $activationCodeType, null, 200);
    }
}
