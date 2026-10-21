<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Graduation\UpdateGraduationStatusRequest;
use App\Http\Resources\Graduation\GraduationRecordResource;
use App\Services\ApiResponseService;
use App\Services\Graduation\GraduationRecordService;
use Illuminate\Http\Request;

class GraduationRecordController extends Controller
{
    protected GraduationRecordService $graduationRecordService;
    public function __construct(GraduationRecordService $graduationRecordService)
    {
        $this->graduationRecordService = $graduationRecordService;
    }
    public function getGraduationRecords(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $records = $this->graduationRecordService->getGraduationRecords($currentSchool);
        return ApiResponseService::success("Graduation Records Fetched Successfully", GraduationRecordResource::collection($records), null, 200);
    }
    public function graduationDetails(Request $request, string $recordId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $record = $this->graduationRecordService->getGraduationRecordDetails($currentSchool, $recordId);
        return ApiResponseService::success("Graduation Record Details Fetched Successfully", $record, null, 200);
    }
    public function blockGraduation(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $blockGraduation = $this->graduationRecordService->blockGraduation($currentSchool, $request->validated());
        return ApiResponseService::success("Graduation Blocked Successfully", $blockGraduation, null, 200);
    }

    public function unBlockGraduation(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $unblockGraduation = $this->graduationRecordService->unBlockGraduation($currentSchool, $request->validated());
        return ApiResponseService::success("Graduation Unblocked Successfully", $unblockGraduation, null, 200);
    }
    public function approveEligibility(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $approveEligibility = $this->graduationRecordService->approveEligibility($currentSchool, $request->validated());
        return ApiResponseService::success("Graduation Eligibility Approved Successfully", $approveEligibility, null, 200);
    }

    public function markInEligible(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $markEligible = $this->graduationRecordService->markInEligible($currentSchool, $request->validated());
        return ApiResponseService::success("Graduation Record Marked As Ineligible Successfully", $markEligible, null, 200);
    }

    public function  requestReview(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $requestReview = $this->graduationRecordService->requestReview($currentSchool, $request->validated());
        return ApiResponseService::success("Request Review Initiated Successfully", $requestReview, null, 200);
    }

    public function approveContinuation(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $approveContinuation = $this->graduationRecordService->approveContinuation($currentSchool, $request->validated());
        return ApiResponseService::success("Continuation Approved Successfully", $approveContinuation, null, 200);
    }

    public function confirmAlumnus(UpdateGraduationStatusRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $confirmAlumnus = $this->graduationRecordService->confirmAlumni($currentSchool, $request->validated());
        return ApiResponseService::success("Alumus Confirmed Successfully", $confirmAlumnus, null, 200);
    }
}
