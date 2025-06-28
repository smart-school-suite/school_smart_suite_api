<?php

namespace App\Http\Controllers;

use App\Http\Requests\TuitionFeeSchedule\CreateFeeScheduleSlotsRequest;
use App\Http\Requests\TuitionFeeSchedule\UpdateFeeScheduleSlotsRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\FeeScheduleService;
use APP\Services\FeeScheduleSlotService;
class FeeScheduleController extends Controller
{
    protected FeeScheduleService $feeScheduleService;
    protected FeeScheduleSlotService $feeScheduleSlotService;

    public function __construct(
        FeeScheduleService $feeScheduleService,
        FeeScheduleSlotService $feeScheduleSlotService
        ){
        $this->feeScheduleService = $feeScheduleService;
        $this->feeScheduleSlotService = $feeScheduleSlotService;
    }

    public function getFeeSchedule(Request $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $feeSchedule = $this->feeScheduleService->getFeeSchedule($currentSchool);
         return ApiResponseService::success("Fee Schedule Fetched Successfully", $feeSchedule, null, 200);
    }

    public function deleteFeeSchedule(Request $request, $feeScheduleId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteFeeSchedule = $this->feeScheduleService->deleteFeeShedule($currentSchool, $feeScheduleId);
        return ApiResponseService::success("Fee Schedule Deleted Successfully", $deleteFeeSchedule, null, 200);
    }

   public function getStudentFeeSchedule(Request $request, $studentId){
      $currentSchool = $request->attributes->get('currentSchool');
      $feeSchedule = $this->feeScheduleService->getStudentFeeSchedule($currentSchool, $studentId);
      return ApiResponseService::success("Student Fee Schedule Fetched Successfully", $feeSchedule, null, 200);
   }

   public function createFeeScheduleSlots(CreateFeeScheduleSlotsRequest $request, $feeScheduleId){
       $currentSchool = $request->attributes->get('currentSchool');
       $scheduleSlots = $this->feeScheduleSlotService->createFeeScheduleSlots($currentSchool, $feeScheduleId, $request->slots);
       return ApiResponseService::success("Fee Schedule Slots Created Successfully", $scheduleSlots, null, 200);
   }

   public function getFeeScheduleSlots(Request $request, $feeScheduleId){
      $currentSchool = $request->attributes->get('currentSchool');
      $scheduleSlots = $this->feeScheduleSlotService->getFeeScheduleSlots($feeScheduleId, $currentSchool);
      return ApiResponseService::success("Fee Schedule Slots Fetched Successfully", $scheduleSlots, null, 200);
   }

   public function updateFeeScheduleSlots(UpdateFeeScheduleSlotsRequest $request, $feeScheduleId){
       $currentSchool = $request->attributes->get('currentSchool');
       $updateSlot = $this->feeScheduleSlotService->updateFeeScheduleSlots($currentSchool, $feeScheduleId, $request->slots);
       return ApiResponseService::success("Fee Schedule Updated Successfully", $updateSlot, null, 200);
   }

}
