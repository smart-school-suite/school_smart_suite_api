<?php

namespace App\Services\StudentPromotion;
use App\Models\Specialty;
use App\Models\Educationlevels;
use App\Models\Student;
use App\Services\ApiResponseService;
class StudentPromotionService
{
        public function promoteStudent(array $data, $currentSchool)
    {
        $findStudent = Student::where('school_branch_id', $currentSchool->id)
            ->with(['level'])
            ->find($data["student_id"]);

        $findSpecialty = Specialty::where('school_branch_id', $currentSchool->id)
            ->where('level_id', $data["level_id"])
            ->find($data["specialty_id"]);

        $findLevel = Educationlevels::find($data["level_id"]);

        if (!$findLevel || !$findStudent || !$findSpecialty) {
            return ApiResponseService::error("The Provided Credentails Are Not Valid");
        }

        if ($findStudent->total_fee_debt > 0) {
            $findStudent->total_fee_debt = $findSpecialty->registration_fee + $findSpecialty->school_fee + $findStudent->total_fee_debt;
        }

        if ($findStudent->total_fee_debt == 0) {
            $findStudent->total_fee_debt = $findSpecialty->registration_fee + $findSpecialty->school_fee;
            $findStudent->fee_status = 'owing';
            $findStudent->specialty_id = $data["specialty_id"];
            $findStudent->level_id = $data["level_id"];
        }

        $findStudent->save();

        return $findStudent;
    }
}
