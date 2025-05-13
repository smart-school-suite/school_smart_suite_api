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
    public function createStudentBatch(CreateStudentBatchRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createStudentBatch = $this->studentBatchService->createStudentBatch($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Batch Created Successfully", $createStudentBatch, null, 201);
    }

    public function updateStudentBatch(UpdateStudentBatchRequest $request)
    {
        $batchId = $request->route('batchId');
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

    public function assignGradDatesBySpecialty(AddGraduationDateRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $assignGraduationDates = $this->studentBatchService->assignGradDatesBySpecialty($currentSchool, $request->graduation_dates);
        return ApiResponseService::success("Graduation Dates for student batches set successfully", $assignGraduationDates, null, 200);
    }

    public function getGraduationDatesByBatch(Request $request, $batchId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getGraduationDatesByBatch = $this->studentBatchService->getGradeDateListByBatch($currentSchool, $batchId);
        return ApiResponseService::success("Graduation Dates Fetched Successfully",  GraduationBatchDateResource::collection($getGraduationDatesByBatch), null, 200);
    }

    public function bulkDeleteStudentBatch($batchIds){
        $idsArray = explode(',', $batchIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_batch,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
         try{
           $bulkDeleteBatch = $this->studentBatchService->bulkDeleteStudentBatch($idsArray);
           return ApiResponseService::success("Student Batch Deleted Successfully", $bulkDeleteBatch, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }

    public function bulkActivateStudentBatch($batchIds){
        $idsArray = explode(',', $batchIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_batch,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
          $bulkActivateBatch = $this->studentBatchService->bulkActivateBatch($idsArray);
          return ApiResponseService::success("Student Batch Activated Successfully", $bulkActivateBatch, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeactivateStudentBatch($batchIds){
        $idsArray = explode(',', $batchIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:student_batch,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeactivateBatch = $this->studentBatchService->bulkDeactivateBatch($idsArray);
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

    public function bulkAssignGradDateBySpecialty(BulkAddGraduationDateRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        try{
           $assignGradDates = $this->studentBatchService->bulkAssignGradeDatesBySpecailty($request->grad_dates, $currentSchool);
           return ApiResponseService::success("Student Graduation Dates Set Succesfully", $assignGradDates, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
