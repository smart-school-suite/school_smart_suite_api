<?php

namespace App\Services\StudentBatch;

use App\Exceptions\AppException;
use App\Models\Studentbatch;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class StudentBatchService
{
    public function getStudentBatchDetails($batchId, $currentSchool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)
            ->find($batchId);
        return $studentBatch;
    }
    public function createStudentBatch(array $data, $currentSchool, $authAdmin)
    {
        $existingBatch = Studentbatch::where("school_branch_id", $currentSchool->id)
            ->where("name", $data["name"])
            ->first();
        if ($existingBatch) {
            throw new AppException(
                "A Student with the same name  already exists.",
                409,
                "Duplicate Student Batch",
                "You are trying to create a Student Batch that already exists. Please check the details and try again.",
                null
            );
        }
        $newBatch = new Studentbatch();
        $newBatch->name = $data["name"];
        $newBatch->description = $data["description"];
        $newBatch->school_branch_id = $currentSchool->id;
        $newBatch->save();
        return $newBatch;
    }

    public function updateStudentBatch(array $data, $studentBatchId, $currentSchool, $authAdmin)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if (!$studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentBatch->update($filteredData);
        return $studentBatch;
    }

    public function bulkUpdateStudentBatch(array $updateDataArray, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataArray as $updateData) {
                $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($updateData['student_batch_id']);
                $filteredData = array_filter($updateData);
                $studentBatch->update($filteredData);
                $result[] = [
                    $studentBatch
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteStudentBatch($studentBatchId, $currentSchool, $authAdmin)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->find($studentBatchId);
        if (!$studentBatch) {
            return ApiResponseService::error("Student Batch Not Found", null, 404);
        }
        $studentBatch->delete();
        return $studentBatch;
    }

    public function bulkDeleteStudentBatch($batchIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($batchIds as $batchId) {
                $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId['student_batch_id']);
                $studentBatch->delete();
                $result[] = $studentBatch;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getStudentBatches($currentSchoool)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchoool->id)->get();
        return $studentBatch;
    }

    public function deactivateBatch($currentSchool, $batchId, $authAdmin)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "inactive";
        $studentBatch->save();
        return $studentBatch;
    }

    public function bulkDeactivateBatch($batchIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($batchIds as $batchId) {
                $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId['student_batch_id']);
                $studentBatch->status = 'inactive';
                $studentBatch->save();
                $result[] = $studentBatch;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function activateBatch($currentSchool, $batchId, $authAdmin)
    {
        $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId);
        $studentBatch->status = "active";
        $studentBatch->save();
        return $studentBatch;
    }

    public function bulkActivateBatch($batchIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($batchIds as $batchId) {
                $studentBatch = Studentbatch::where("school_branch_id", $currentSchool->id)->findOrFail($batchId['student_batch_id']);
                $studentBatch->status = 'active';
                $studentBatch->save();
                $result[] = $studentBatch;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
