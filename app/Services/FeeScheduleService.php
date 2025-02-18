<?php

namespace App\Services;

use App\Models\SchoolFeeSchedule;

class FeeScheduleService
{
    // Implement your logic here

    public function scheduleFeePayment(array $feeSchedule, $currentSchool){
         $result = [];
         foreach($feeSchedule as $schedule){
           $createdSchoolFeeSchedule = SchoolFeeSchedule::create([
                'school_branch_id' => $currentSchool->id,
                'specialty_id' => $schedule['specialty_id'],
                'title' => $schedule['title'],
                'amount' => $schedule['amount'],
                'deadline_date' => $schedule['deadline_date']
            ]);

            $result[$schedule] = $createdSchoolFeeSchedule;
         }

         return $result;
    }

    public function deleteSchedule($scheduleId, $currentSchool){
        $feeScheduleExists = SchoolFeeSchedule::where("school_branch_id", $currentSchool->id)->find($scheduleId);
        if(!$feeScheduleExists){
            return ApiResponseService::error("Schedule Not Found I think It must have been deleted");
        }
        $feeScheduleExists->delete();
        return $feeScheduleExists;
    }

    public function getFeePaymentSchedule(string $specialtyId, $currentSchool){
        $specailtyFeePaymentSchedule = SchoolFeeSchedule::where("school_branch_id", $currentSchool->id)->where("specialty_id", $specialtyId)->get();
        return $specailtyFeePaymentSchedule;
    }
}
