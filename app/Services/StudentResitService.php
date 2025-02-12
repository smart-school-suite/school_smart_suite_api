<?php

namespace App\Services;

use App\Models\Studentresit;

class StudentResitService
{
    // Implement your logic here
    public function updateStudentResit(array $data, $currentSchool, $studentResitId)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentResitExists->update($filteredData);
        return $studentResitExists;
    }

    public function deleteStudentResit($studentResitId, $currentSchool)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $studentResitExists->delete();
        return $studentResitExists;
    }

    public function getStudentResits($currentSchool)
    {
        $getStudentResits = Studentresit::where('school_branch_id', $currentSchool->id)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->get();
        return $getStudentResits;
    }

    public function getStudentResitDetails($currentSchool, $studentResitId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->find($studentResitId);
        return $getResitData;
    }

    public function getMyResits($currentSchool, $examId, $studentId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->where("exam_id", $examId)
            ->where("student_id", $studentId)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->get();
        return $getResitData;
    }

    public function payResit($currentSchool, $studentResitId)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $studentResitExists->paid_status = "Paid";
        $studentResitExists->save();

        return $studentResitExists;
    }
}
