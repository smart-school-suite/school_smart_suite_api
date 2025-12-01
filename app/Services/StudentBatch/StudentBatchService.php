<?php

namespace App\Services\StudentBatch;

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
        $newBatch = new Studentbatch();
        $newBatch->name = $data["name"];
        $newBatch->description = $data["description"];
        $newBatch->school_branch_id = $currentSchool->id;
        $newBatch->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.student.batch.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "studentBatchManagement",
                "authAdmin" => $authAdmin,
                "data" => $newBatch,
                "message" => "Student Batch Created",
            ]
        );
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.student.batch.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "studentBatchManagement",
                "authAdmin" => $authAdmin,
                "data" => $studentBatch,
                "message" => "Student Batch Updated",
            ]
        );
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.student.batch.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "studentBatchManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Student Batch Updated",
                ]
            );
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.student.batch.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "studentBatchManagement",
                "authAdmin" => $authAdmin,
                "data" => $studentBatch,
                "message" => "Student Batch Deleted",
            ]
        );
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.student.batch.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "studentBatchManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Student Batch Deleted",
                ]
            );
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.student.batch.deactivate"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "studentBatchManagement",
                "authAdmin" => $authAdmin,
                "data" => $studentBatch,
                "message" => "Student Batch Deactivated",
            ]
        );
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.student.batch.deactivate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "studentBatchManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Student Batch Deactivated",
                ]
            );
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.student.batch.activate"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "studentBatchManagement",
                "authAdmin" => $authAdmin,
                "data" => $studentBatch,
                "message" => "Student Batch Activated",
            ]
        );
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.student.batch.activate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "studentBatchManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Student Batch Activated",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
