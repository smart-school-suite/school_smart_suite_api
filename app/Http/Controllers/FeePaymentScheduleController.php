<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FeePaymentScheduleService;
use App\Http\Requests\TuitionFee\CreateTuitionFeeScheduleRequest;
use App\Http\Requests\TuitionFee\UpdateTuitionFeeScheduleRequest;
use App\Http\Resources\FeePaymentScheduleResource;
use App\Services\ApiResponseService;

class FeePaymentScheduleController extends Controller
{
    //
    protected FeePaymentScheduleService $feePaymentScheduleService;
    public function __construct(FeePaymentScheduleService $feePaymentScheduleService){
        $this->feePaymentScheduleService = $feePaymentScheduleService;
    }

    public function createFeePaymentSchedule(CreateTuitionFeeScheduleRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createFeePaymentSchedule = $this->feePaymentScheduleService->createFeePaymentSchedule($request->validated(), $currentSchool);
        return ApiResponseService::success("Fee Payment Schedule Created Succesfully", $createFeePaymentSchedule, null, 201);
    }

    public function updateFeePaymentSchedule(UpdateTuitionFeeScheduleRequest $request, string $scheduleId){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateFeePaymentService = $this->feePaymentScheduleService->updateFeePaymentSchedule($request->validated(), $currentSchool, $scheduleId);
        return ApiResponseService::success("Fee Payment Schedule Updated Succesfully", $updateFeePaymentService, null, 200);
    }

    public function deleteFeePaymentSchedule(Request $request, string $scheduleId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteFeePaymentSchedule = $this->feePaymentScheduleService->deleteFeePaymentSchedule($currentSchool, $scheduleId);
        return ApiResponseService::success("Fee Payment Schedule Deleted Succesfully", $deleteFeePaymentSchedule, null, 200);
    }

    public function getFeePaymentScheduleBySpecialty(Request $request, string $specialtyId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getBySpecialty = $this->feePaymentScheduleService->getFeePaymentScheduleBySpecialty($currentSchool, $specialtyId);
        return ApiResponseService::success("Fee Payment Schedule Fetched Succefully", FeePaymentScheduleResource::collection($getBySpecialty), null, 200);
    }

    public function getAllFeePaymentSchedule(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getAllFeePaymentSchedule = $this->feePaymentScheduleService->getAllFeePaymentSchedule($currentSchool);
        return ApiResponseService::success("Fee Payment Schedule Fetched Succesfully", FeePaymentScheduleResource::collection($getAllFeePaymentSchedule), null, 200);
    }
}
