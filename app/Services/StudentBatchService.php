<?php

namespace App\Services;

use App\Models\Studentbatch;
use App\Models\StudentBatchGradeDates;

class StudentBatchService
{
    // Implement your logic here
    public function createStudentBatch(array $data, $currentSchool)
    {
        $newBatch = new Studentbatch();
        $newBatch->name = $data["name"];
        $newBatch->description = $data["description"];
        $newBatch->school_branch_id = $currentSchool->id;
        $newBatch->save();
        return $newBatch;
    }

    public function updateStudentBatch(array $data, $studentBatchId, $currentSchool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if ($studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentBatch->update($filteredData);
        return $studentBatch;
    }

    public function deleteStudentBatch($studentBatchId, $currentSchool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if ($studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $studentBatch->delete();
        return $studentBatch;
    }

    public function getStudentBatches($currentSchoool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchoool->id)->get();
        return $studentBatch;
    }

    public function deactivateBatch($currentSchool, $batchId){
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "inactive";
        $studentBatch->save();
        return $studentBatch;
    }

    public function activateBatch($currentSchool, $batchId){
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "active";
        $studentBatch->save();
        return $studentBatch;
    }

    public function assignGradDatesBySpecialty($currentSchool, array $gradDates) {
        $result = [];

        foreach ($gradDates as $gradDate) {

            $graduationDate = new StudentBatchGradeDates();
            $graduationDate->graduation_date = $gradDate['graduation_date'];
            $graduationDate->specialty_id = $gradDate['specialty_id'];
            $graduationDate->level_id = $gradDate['level_id'];
            $graduationDate->student_batch_id = $gradDate['student_batch_id'];
            $graduationDate->school_branch_id = $currentSchool->id;
             $graduationDate->save();
            $result[] = [
                'graduation_date' => $graduationDate->graduation_date,
                'specialty_id' => $graduationDate->specialty_id,
                'level_id' => $graduationDate->level_id,
                'student_batch_id' => $graduationDate->student_batch_id,
            ];
        }

        return $result;
    }

    public function getGradeDateListByBatch($currentSchool, $batchId){
        $gradeDates = StudentBatchGradeDates::where("school_branch_id", $currentSchool->id)
                                             ->with(['specialty', 'studentBatch', 'level'])
                                             ->where("student_batch_id", $batchId)->get();
        return $gradeDates;
    }

}
