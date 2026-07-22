<?php

namespace App\Http\Controllers\Qualification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Qualification\CreateQualificationRequest;
use App\Http\Requests\Qualification\UpdateQualificationRequest;
use App\Services\ApiResponseService;
use App\Services\Qualification\QualificationService;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    protected QualificationService $qualificationService;
    public function __construct(QualificationService $qualificationService)
    {
        $this->qualificationService = $qualificationService;
    }
    public function getAllQualifications(Request $request)
    {
        $qualifications = $this->qualificationService->getAllQualifications();
        return ApiResponseService::success("All Qualifications Fetched Successfully", $qualifications, null, 200);
    }
    public function getActiveQualifications(Request $request)
    {
        $activeQualifications = $this->qualificationService->getActiveQualifications();
        return ApiResponseService::success("All Active Qualifications Fetched Successfully", $activeQualifications, null, 200);
    }
    public function getQualificationDetails(Request $request, string $qualificationId)
    {
        $qualifactionDetails = $this->qualificationService->getQualificationDetails($qualificationId);
        return ApiResponseService::success("Qualification Details Fetched Successfully", $qualifactionDetails, null, 200);
    }
    public function createQualification(CreateQualificationRequest $request)
    {
        $createQualification = $this->qualificationService->createQualification($request->validated());
        return ApiResponseService::success("Qualification Created Successfully", $createQualification, null, 201);
    }
    public function updateQualification(UpdateQualificationRequest $request, string $qualificationId)
    {
        $updateQualification = $this->qualificationService->updateQualification($qualificationId, $request->validated());
        return ApiResponseService::success("Qualification Updated Successfully", $updateQualification, null, 200);
    }
    public function deleteQualification(Request $request, string  $qualificationId)
    {
        $deleteQualification = $this->qualificationService->deleteQualification($qualificationId);
        return ApiResponseService::success("Qualification Deleted Successfully", $deleteQualification, null, 200);
    }
    public function deactivateQualification(Request $request, string $qualificationId)
    {
        $deactivateQualification = $this->qualificationService->deactivateQualification($qualificationId);
        return ApiResponseService::success("Qualification Deactivated Successfully", $deactivateQualification, null, 200);
    }
    public function activateQualification(Request $request, string $qualificationId)
    {
        $activateQualifcation = $this->qualificationService->activateQualification($qualificationId);
        return ApiResponseService::success("Qualication Activated Successfully", $activateQualifcation, null, 200);
    }
}
