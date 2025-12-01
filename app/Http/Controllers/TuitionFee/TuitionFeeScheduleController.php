<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeeScheduleService;
use App\Http\Requests\TuitionFeeSchedule\AutoCreateFeePaymentScheduleRequest;
use App\Http\Resources\FeeScheduleResource;
use App\Services\ApiResponseService;

class TuitionFeeScheduleController extends Controller
{
    protected TuitionFeeScheduleService $tuitionFeeScheduleService;

    public function __construct(TuitionFeeScheduleService $tuitionFeeScheduleService)
    {
        $this->tuitionFeeScheduleService = $tuitionFeeScheduleService;
    }
    public function autoCreateFeePaymentSchedule(AutoCreateFeePaymentScheduleRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feeSchedule = $this->tuitionFeeScheduleService->autoCreateFeePaymentSchedule($currentSchool, $request->validated());
        return ApiResponseService::success("Shedule Generated Successfully", $feeSchedule, null, 200);
    }
    public function getFeeSchedule(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feeSchedule = $this->tuitionFeeScheduleService->getFeeSchedule($currentSchool);
        return ApiResponseService::success("Fee Schedule Fetched Successfully", FeeScheduleResource::collection($feeSchedule), null, 200);
    }

    public function deleteFeeSchedule(Request $request, $feeScheduleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deleteFeeSchedule = $this->tuitionFeeScheduleService->deleteFeeShedule($currentSchool, $feeScheduleId, $authAdmin);
        return ApiResponseService::success("Fee Schedule Deleted Successfully", $deleteFeeSchedule, null, 200);
    }

    public function getFeeScheduleStudentId(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feeSchedule = $this->tuitionFeeScheduleService->getFeeScheduleStudentId($currentSchool, $studentId);
        return ApiResponseService::success("Student Fee Schedule Fetched Successfully", $feeSchedule, null, 200);
    }

    public function getStudentFeeSchedule(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $feeSchedule = $this->tuitionFeeScheduleService->getStudentFeeSchedule($currentSchool, $authStudent);
        return ApiResponseService::success("Student Fee Schedule Fetched Successfully", $feeSchedule, null, 200);
    }

    public function getStudentFeeScheduleLevelId(Request $request, $levelId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $feeSchedule = $this->tuitionFeeScheduleService->getStudentFeeScheduleLevelId($currentSchool, $authStudent, $levelId);
        return ApiResponseService::success("Student Fee Schedule Fetched Successfully", $feeSchedule, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
