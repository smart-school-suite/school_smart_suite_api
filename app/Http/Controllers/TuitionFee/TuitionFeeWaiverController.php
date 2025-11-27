<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TuitionFee\CreateTuitionFeeWaiverRequest;
use App\Http\Requests\TuitionFee\UpdateTuitionFeeWaiverRequest;
use App\Services\ApiResponseService;
use App\Services\TuitionFee\TuitionFeeWaiverService;

class TuitionFeeWaiverController extends Controller
{
    protected TuitionFeeWaiverService $feeWaiverService;
    public function __construct(TuitionFeeWaiverService $feeWaiverService)
    {
        $this->feeWaiverService = $feeWaiverService;
    }

    public function  createFeeWaiver(CreateTuitionFeeWaiverRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $createFeeWaiver = $this->feeWaiverService->createFeeWaiver($request->validated(), $currentSchool);
        return ApiResponseService::success("Fee Waiver Created Sucessfully", $createFeeWaiver, null, 201);
    }

    public function updateFeeWaiver(UpdateTuitionFeeWaiverRequest $request, string $feeWaiverId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $updateFeeWaiver = $this->feeWaiverService->updateFeeWaiver($request->validated(), $currentSchool, $feeWaiverId);
        return ApiResponseService::success("Fee Waiver Updated Succesfully", $updateFeeWaiver, null, 200);
    }

    public function deleteFeeWaiver(Request $request, string $feeWaiverId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteFeeWaiver = $this->feeWaiverService->deleteFeeWaiver($currentSchool, $feeWaiverId);
        return ApiResponseService::success("Fee Waiver Deleted Succesfully", $deleteFeeWaiver, null, 200);
    }

    public function getByStudent(Request $request, string $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getFeeWaiver = $this->feeWaiverService->getFeeWaiverByStudent($studentId, $currentSchool);
        return ApiResponseService::success("Fee Waiver Fetched Sucessfully", $getFeeWaiver, null, 200);
    }

    public function getAllFeeWaiver(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getAllFeeWaivers = $this->feeWaiverService->getAllFeeWaiver($currentSchool);
        return ApiResponseService::success("Fee Waiver Fetched Successfully", $getAllFeeWaivers, null, 200);
    }
}
