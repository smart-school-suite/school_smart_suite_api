<?php

namespace App\Http\Controllers\AdditionalFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdditionalFeeCategory\CreateAdditionalFeeCategoryRequest;
use App\Http\Requests\AdditionalFeeCategory\UpdateAdditionalFeeCategoryRequest;
use App\Services\AdditionalFee\AdditionalFeeCategoryService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class AdditionalFeeCategoryController extends Controller
{
    protected AdditionalFeeCategoryService $additionalFeeCategoryService;
    public function __construct(AdditionalFeeCategoryService $additionalFeeCategoryService)
    {
        $this->additionalFeeCategoryService = $additionalFeeCategoryService;
    }

    public function createAddtionalFeeCategory(CreateAdditionalFeeCategoryRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createAdditionalFee = $this->additionalFeeCategoryService->createAdditionalFeeCategory($request->validated(), $currentSchool);
        return ApiResponseService::success("Additional Fee Category Created Sucessfully", $createAdditionalFee, null, 201);
    }

    public function deleteAdditionalFeeCategory(Request $request, string $feeCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->additionalFeeCategoryService->deleteAdditionalFeeCategory($currentSchool, $feeCategoryId);
        return ApiResponseService::success("Additionla Fee Category Deleted Succesfully", null, null, 200);
    }

    public function updateAdditionalFeeCategory(UpdateAdditionalFeeCategoryRequest $request, string $feeCategoryId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateAdditionalFeeCategory = $this->additionalFeeCategoryService->updateAdditionalFeeCategory($request->validated(), $currentSchool, $feeCategoryId);
        return ApiResponseService::success("Additional Fee Category Updated Sucessfully", $updateAdditionalFeeCategory, null, 200);
    }

    public function getAdditionalFeeCategory(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFeeCategory = $this->additionalFeeCategoryService->getAdditionalFeeCategory($currentSchool);
        return ApiResponseService::success("Additional Fee Category Fetched Succesfully", $getAdditionalFeeCategory, null, 200);
    }
}
