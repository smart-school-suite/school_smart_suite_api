<?php

namespace App\Services;

use App\Models\AdditionalFees;
use App\Models\Student;

class studentAdditionalFeeService
{
    // Implement your logic here

    public function createStudentAdditionalFees(array $additionalFees, $currentSchool)
    {
        $studentAdditionFees = new AdditionalFees();
        $studentAdditionFees->title = $additionalFees['title'];
        $studentAdditionFees->reason = $additionalFees['reason'];
        $studentAdditionFees->amount = $additionalFees['amount'];
        $studentAdditionFees->school_branch_id = $currentSchool->id;
        $studentAdditionFees->specialty_id = $additionalFees['specialty_id'];
        $studentAdditionFees->level_id = $additionalFees['level_id'];
        $studentAdditionFees->student_id = $additionalFees['student_id'];
        $studentAdditionFees->save();
        return $studentAdditionFees;
    }

    public function deleteStudentAdditionalFees(string $feeId, $currentSchool)
    {
        $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        if (!$studentAdditionFeesExist) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }
        $studentAdditionFeesExist->delete();
        return $studentAdditionFeesExist;
    }

    public function updateStudentAdditionalFees(array $additionalFeesData, string $feeId, $currentSchool)
    {
        $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        if (!$studentAdditionFeesExist) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }

        $removedEmptyInputs = array_filter($additionalFeesData);
        $studentAdditionFeesExist->update($removedEmptyInputs);
        return $studentAdditionFeesExist;
    }

    public function getStudentAdditionalFees(string $studentId, $currentSchool)
    {
        $studentAdditionFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->where("student_id", $studentId)->with(['student', 'specialty', 'level'])->get();
        return $studentAdditionFees;
    }

    public function getAdditionalFees($currentSchool)
    {
        $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level'])->get();
        return $additionalFees;
    }
}
