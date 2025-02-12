<?php

namespace App\Services;

use App\Models\Studentbatch;

class StudentBatchService
{
    // Implement your logic here
    public function createStudentBatch(array $data, $currentSchool)
    {
        $new_student_batch_instance = new Studentbatch();
        $new_student_batch_instance->name = $data["name"];
        $new_student_batch_instance->graduation_date = $data["graduation_date"];
        $new_student_batch_instance->school_branch_id = $currentSchool->id;
        $new_student_batch_instance->save();
        return $new_student_batch_instance;
    }

    public function updateStudentBatch(array $data, $studentBatchId, $currentSchool)
    {
        $studentBatchExist = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if ($studentBatchExist) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentBatchExist->update($filteredData);
        return $studentBatchExist;
    }

    public function deleteStudentBatch($studentBatchId, $currentSchool)
    {
        $studentBatchExist = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if ($studentBatchExist) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $studentBatchExist->delete();
        return $studentBatchExist;
    }

    public function getStudentBatches($currentSchoool)
    {
        $studentBatchExist = Studentbatch::where("school_branch_id", $currentSchoool->id)->get();
        return $studentBatchExist;
    }
}
