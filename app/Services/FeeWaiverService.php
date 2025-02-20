<?php

namespace App\Services;
use App\Models\FeeWaiver;
class FeeWaiverService
{
    // Implement your logic here

    public function createFeeWaiver(array $feeWaiverData, $currentSchool)
    {
        $feeWaiver = new FeeWaiver();
        $feeWaiver->start_date = $feeWaiverData['start_date'];
        $feeWaiver->end_date = $feeWaiverData['end_date'];
        $feeWaiver->description = $feeWaiverData['description'];
        $feeWaiver->specialty_id = $feeWaiverData['specialty_id'];
        $feeWaiver->level_id = $feeWaiverData['level_id'];
        $feeWaiver->student_id = $feeWaiverData['student_id'];
        $feeWaiver->school_branch_id = $currentSchool->id;
        $feeWaiver->save();
        return $feeWaiver;
    }

    public function updateFeeWaiver(array $feeWaiverData, $currentSchool, string $feeWaiverId)
    {
        $waiverExists = FeeWaiver::where("school_branch_id", $currentSchool->id)->find($feeWaiverId);
        if (!$waiverExists) {
            return ApiResponseService::error("Waiver Not found it might have been deleted", null, 404);
        }
        $filteredEmptyEntries = array_filter($feeWaiverData);
        $waiverExists->update($filteredEmptyEntries);
        return $waiverExists;
    }

    public function deleteFeeWaiver(string $feeWaiverId, $currentSchool)
    {
        $waiverExists = FeeWaiver::where("school_branch_id", $currentSchool->id)->find($feeWaiverId);
        if (!$waiverExists) {
            return ApiResponseService::error("Waiver Not found it might have been deleted", null, 404);
        }
        $waiverExists->delete();
        return $waiverExists;
    }

    public function getFeeWaiverByStudent(string $studentId, $currentSchool)
    {
        $getFeeWaiver = FeeWaiver::where("school_branch_id", $currentSchool->id)->where("student_id", $studentId)
            ->with(['specialty', 'level', 'student'])
            ->get();
        return $getFeeWaiver;
    }

    public function getAllFeeWaiver($currentSchool)
    {
        $getFeeWaiver = FeeWaiver::where("school_branch_id", $currentSchool->id)->with(['specialty', 'level', 'student'])->get();
        return $getFeeWaiver;
    }
}
