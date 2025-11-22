<?php

namespace App\Http\Controllers;


use App\Http\Requests\BulkAssignGradeDatesRequest;
use App\Services\StudentBatchService;
use App\Http\Resources\GraduationBatchDateResource;
use App\Http\Requests\StudentBatch\CreateStudentBatchRequest;
use App\Http\Requests\StudentBatch\UpdateStudentBatchRequest;
use App\Http\Requests\StudentBatch\AddGraduationDateRequest;
use App\Http\Requests\StudentBatch\BulkUpdateStudentBatchRequest;
use App\Http\Requests\StudentBatch\BulkAddGraduationDateRequest;
use App\Http\Requests\StudentBatch\StudentBatchIdRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;

class StudentBatchcontroller extends Controller
{
    //
    protected StudentBatchService $studentBatchService;
    public function __construct(StudentBatchService $studentBatchService)
    {
        $this->studentBatchService = $studentBatchService;
    }
    public function getStudentBatchDetails(Request $request, $batchId){
        $currentSchool = $request->attributes->get('currentSchool');
        $batchDetails = $this->studentBatchService->getStudentBatchDetails($batchId, $currentSchool);
        return ApiResponseService::success("Student Batch Details Fetched Successfully", $batchDetails, null, 200);
    }
    public function createStudentBatch(CreateStudentBatchRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createStudentBatch = $this->studentBatchService->createStudentBatch($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Batch Created Successfully", $createStudentBatch, null, 201);
    }

    public function updateStudentBatch(UpdateStudentBatchRequest $request, $batchId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentBatch = $this->studentBatchService->updateStudentBatch($request->validated(), $batchId, $currentSchool);
        return ApiResponseService::success('Student Batch Updated Successfully', $updateStudentBatch, null, 200);
    }

    public function deleteStudentBatch(Request $request)
    {
        $batchId = $request->route('batchId');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentBatches = $this->studentBatchService->deleteStudentBatch($batchId, $currentSchool);
        return ApiResponseService::success("Student Batch Deleted Successully", $deleteStudentBatches, null, 200);
    }

    public function getStudentBatch(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentBatches = $this->studentBatchService->getStudentBatches($currentSchool);
        return ApiResponseService::success('Student Batch Fetched Succefully', $getStudentBatches,  null, 200);
    }

    public function activateStudentBatch(Request $request, $batchId){
        $currentSchool = $request->attributes->get('currentSchool');
        $activateStudentBatch = $this->studentBatchService->activateBatch($currentSchool, $batchId);
        return ApiResponseService::success("Student Batch Activated Succesfully", $activateStudentBatch, null, 200);
    }

    public function deactivateStudentBatch(Request $request, $batchId){
        $currentSchool = $request->attributes->get('currentSchool');
        $DeactivateStudentBatch = $this->studentBatchService->deactivateBatch($currentSchool, $batchId);
        return ApiResponseService::success("Student Batch Deactivated Succesfully", $DeactivateStudentBatch, null, 200);
    }

    public function bulkDeleteStudentBatch(StudentBatchIdRequest $request){
         try{
           $bulkDeleteBatch = $this->studentBatchService->bulkDeleteStudentBatch($request->studentBatchIds);
           return ApiResponseService::success("Student Batch Deleted Successfully", $bulkDeleteBatch, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }

    public function bulkActivateStudentBatch(StudentBatchIdRequest $request){
        try{
          $bulkActivateBatch = $this->studentBatchService->bulkActivateBatch($request->studentBatchIds);
          return ApiResponseService::success("Student Batch Activated Successfully", $bulkActivateBatch, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeactivateStudentBatch(StudentBatchIdRequest $request){
        try{
           $bulkDeactivateBatch = $this->studentBatchService->bulkDeactivateBatch($request->studentBatchIds);
           return ApiResponseService::success("Student Batch Deactivated Succesfully", $bulkDeactivateBatch, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateStudentBatch(BulkUpdateStudentBatchRequest $request){
        try{
            $bulkUpdateBatch = $this->studentBatchService->bulkUpdateStudentBatch($request->student_batches);
            return ApiResponseService::success("Student Batch Updated Successfully", $bulkUpdateBatch, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
