<?php

namespace App\Services\StudentBatch;
use App\Models\Studentbatch;
use Illuminate\Support\Facades\DB;
use App\Models\StudentBatchGradeDates;
use Exception;
use App\Services\ApiResponseService;
class StudentBatchService
{
        public function getStudentBatchDetails($batchId, $currentSchool){
         $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)
                              ->find($batchId);
         return $studentBatch;
    }
    public function createStudentBatch(array $data, $currentSchool)
    {
        $newBatch = new Studentbatch();
        $newBatch->name = $data["name"];
        $newBatch->description = $data["description"] ;
        $newBatch->school_branch_id = $currentSchool->id;
        $newBatch->save();
        return $newBatch;
    }

    public function updateStudentBatch(array $data, $studentBatchId, $currentSchool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if (!$studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentBatch->update($filteredData);
        return $studentBatch;
    }

    public function bulkUpdateStudentBatch(array $updateDataArray){
        $result = [];
        try{
            DB::beginTransaction();
           foreach($updateDataArray as $updateData){
              $studentBatch = Studentbatch::findOrFail($updateData['student_batch_id']);
              $filteredData = array_filter($updateData);
              $studentBatch->update($filteredData);
              $result[] = [
                 $studentBatch
              ];
           }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
           DB::rollBack();
           throw $e;
        }
    }

    public function deleteStudentBatch($studentBatchId, $currentSchool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if (!$studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $studentBatch->delete();
        return $studentBatch;
    }

    public function bulkDeleteStudentBatch($batchIds){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($batchIds as $batchId){
                $studentBatch = Studentbatch::findOrFail($batchId['student_batch_id']);
                $studentBatch->delete();
                $result[] = $studentBatch;
            }
            DB::commit();
           return $result;
        }
        catch(Exception $e){
           DB::rollBack();
           throw $e;
        }
    }
    public function getStudentBatches($currentSchoool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchoool->id)->get();
        return $studentBatch;
    }

    public function deactivateBatch($currentSchool, $batchId)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "inactive";
        $studentBatch->save();
        return $studentBatch;
    }

    public function bulkDeactivateBatch($batchIds){
         $result = [];
         try{
            DB::beginTransaction();
            foreach($batchIds as $batchId){
                $studentBatch = Studentbatch::findOrFail($batchId['student_batch_id']);
                $studentBatch->status = 'inactive';
                $studentBatch->save();
                $result[] = $studentBatch;

            }
            DB::commit();
            return $result;
         }
         catch(Exception $e){
            DB::rollBack();
            throw $e;
         }
    }

    public function activateBatch($currentSchool, $batchId)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "active";
        $studentBatch->save();
        return $studentBatch;
    }

    public function bulkActivateBatch($batchIds){
        $result = [];
        try{
            DB::beginTransaction();
           foreach($batchIds as $batchId){
            $studentBatch = Studentbatch::findOrFail($batchId['student_batch_id']);
            $studentBatch->status = 'active';
            $studentBatch->save();
            $result[] = $studentBatch;
           }
           DB::commit();
            return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
