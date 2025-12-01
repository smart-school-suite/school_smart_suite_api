<?php

namespace App\Http\Controllers\StudentBatch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StudentBatch\CreateStudentBatchRequest;
use App\Http\Requests\StudentBatch\UpdateStudentBatchRequest;
use App\Http\Requests\StudentBatch\BulkUpdateStudentBatchRequest;
use App\Http\Requests\StudentBatch\StudentBatchIdRequest;
use App\Services\ApiResponseService;
use App\Services\StudentBatch\StudentBatchService;
use Exception;

class StudentBatchController extends Controller
{
    protected StudentBatchService $studentBatchService;
    public function __construct(StudentBatchService $studentBatchService)
    {
        $this->studentBatchService = $studentBatchService;
    }
    public function getStudentBatchDetails(Request $request, $batchId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $batchDetails = $this->studentBatchService->getStudentBatchDetails($batchId, $currentSchool);
        return ApiResponseService::success("Student Batch Details Fetched Successfully", $batchDetails, null, 200);
    }
    public function createStudentBatch(CreateStudentBatchRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $createStudentBatch = $this->studentBatchService->createStudentBatch($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Created Successfully", $createStudentBatch, null, 201);
    }
    public function updateStudentBatch(UpdateStudentBatchRequest $request, $batchId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentBatch = $this->studentBatchService->updateStudentBatch($request->validated(), $batchId, $currentSchool, $authAdmin);
        return ApiResponseService::success('Student Batch Updated Successfully', $updateStudentBatch, null, 200);
    }
    public function deleteStudentBatch(Request $request)
    {
        $batchId = $request->route('batchId');
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentBatches = $this->studentBatchService->deleteStudentBatch($batchId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Deleted Successully", $deleteStudentBatches, null, 200);
    }
    public function getStudentBatch(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentBatches = $this->studentBatchService->getStudentBatches($currentSchool);
        return ApiResponseService::success('Student Batch Fetched Succefully', $getStudentBatches,  null, 200);
    }
    public function activateStudentBatch(Request $request, $batchId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateStudentBatch = $this->studentBatchService->activateBatch($currentSchool, $batchId, $authAdmin);
        return ApiResponseService::success("Student Batch Activated Succesfully", $activateStudentBatch, null, 200);
    }
    public function deactivateStudentBatch(Request $request, $batchId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $DeactivateStudentBatch = $this->studentBatchService->deactivateBatch($currentSchool, $batchId, $authAdmin);
        return ApiResponseService::success("Student Batch Deactivated Succesfully", $DeactivateStudentBatch, null, 200);
    }
    public function bulkDeleteStudentBatch(StudentBatchIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeleteBatch = $this->studentBatchService->bulkDeleteStudentBatch($request->studentBatchIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Deleted Successfully", $bulkDeleteBatch, null, 200);
    }
    public function bulkActivateStudentBatch(StudentBatchIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkActivateBatch = $this->studentBatchService->bulkActivateBatch($request->studentBatchIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Activated Successfully", $bulkActivateBatch, null, 200);
    }
    public function bulkDeactivateStudentBatch(StudentBatchIdRequest $request)
    {

        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeactivateBatch = $this->studentBatchService->bulkDeactivateBatch($request->studentBatchIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Deactivated Succesfully", $bulkDeactivateBatch, null, 200);
    }
    public function bulkUpdateStudentBatch(BulkUpdateStudentBatchRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkUpdateBatch = $this->studentBatchService->bulkUpdateStudentBatch($request->student_batches, $currentSchool, $authAdmin);
        return ApiResponseService::success("Student Batch Updated Successfully", $bulkUpdateBatch, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
