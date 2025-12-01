<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeeScheduleSlotService;
use App\Http\Requests\TuitionFeeSchedule\CreateFeeScheduleSlotsRequest;
use App\Http\Requests\TuitionFeeSchedule\UpdateFeeScheduleSlotsRequest;
use App\Services\ApiResponseService;

class TuitionFeeScheduleSlotController extends Controller
{
    protected TuitionFeeScheduleSlotService $tuitionFeeScheduleSlotService;

    public function __construct(TuitionFeeScheduleSlotService $tuitionFeeScheduleSlotService)
    {
        $this->tuitionFeeScheduleSlotService = $tuitionFeeScheduleSlotService;
    }

    public function createFeeScheduleSlots(CreateFeeScheduleSlotsRequest $request, $feeScheduleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $scheduleSlots = $this->tuitionFeeScheduleSlotService->createFeeScheduleSlots($currentSchool, $feeScheduleId, $request->slots, $authAdmin);
        return ApiResponseService::success("Fee Schedule Slots Created Successfully", $scheduleSlots, null, 200);
    }

    public function getFeeScheduleSlots(Request $request, $feeScheduleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $scheduleSlots = $this->tuitionFeeScheduleSlotService->getFeeScheduleSlots($feeScheduleId, $currentSchool);
        return ApiResponseService::success("Fee Schedule Slots Fetched Successfully", $scheduleSlots, null, 200);
    }

    public function updateFeeScheduleSlots(UpdateFeeScheduleSlotsRequest $request, $feeScheduleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $updateSlot = $this->tuitionFeeScheduleSlotService->updateFeeScheduleSlots($currentSchool, $feeScheduleId, $request->slots, $authAdmin);
        return ApiResponseService::success("Fee Schedule Updated Successfully", $updateSlot, null, 200);
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
