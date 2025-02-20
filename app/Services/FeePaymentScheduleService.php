<?php

namespace App\Services;
use App\Models\FeePaymentSchedule;
class FeePaymentScheduleService
{
    // Implement your logic here
    public function createFeePaymentSchedule($scheduleData, $currentSchool)
    {
        $feePaymentSchedule = new FeePaymentSchedule();
        $feePaymentSchedule->title = $scheduleData['title'];
        $feePaymentSchedule->num_installments = $scheduleData['num_installments'];
        $feePaymentSchedule->amount = $scheduleData['amount'];
        $feePaymentSchedule->due_date = $scheduleData['due_date'];
        $feePaymentSchedule->type = $scheduleData['type'];
        $feePaymentSchedule->school_branch_id = $currentSchool->id;
        $feePaymentSchedule->specialty_id = $scheduleData['specialty_id'];
        $feePaymentSchedule->level_id = $scheduleData['level_id'];
        $feePaymentSchedule->save();
        return $feePaymentSchedule;
    }

    public function updateFeePaymentSchedule($scheduleData, $currentSchool, $sheduleId)
    {
        $feePaymentScheduleExists = FeePaymentSchedule::where('school_branch_id', $currentSchool->id)->find($sheduleId);
        if (!$feePaymentScheduleExists) {
            return ApiResponseService::error("Fee Payment Schedule Not Found", null, 404);
        }
        $removedEmptyEntries = array_filter($scheduleData);
        $feePaymentScheduleExists->update($removedEmptyEntries);
        return $feePaymentScheduleExists;
    }

    public function deleteFeePaymentSchedule($currentSchool, $sheduleId)
    {
        $feePaymentScheduleExists = FeePaymentSchedule::where('school_branch_id', $currentSchool->id)->find($sheduleId);
        if (!$feePaymentScheduleExists) {
            return ApiResponseService::error("Fee Payment Schedule Not Found", null, 404);
        }
        $feePaymentScheduleExists->delete();
        return $feePaymentScheduleExists;
    }

    public function getFeePaymentScheduleBySpecialty($currentSchool, $specialtyId)
    {
        $feePaymentSchedule = FeePaymentSchedule::where("school_branch_id", $currentSchool->id)
            ->where('specialty_id', $specialtyId)->with(['specialty', 'level'])->get();
        return $feePaymentSchedule;
    }

    public function getAllFeePaymentSchedule($currentSchool)
    {
        $feePaymentSchedule = FeePaymentSchedule::where("school_branch_id", $currentSchool->id)
            ->with(['specialty', 'level'])->get();
        return $feePaymentSchedule;
    }
}
