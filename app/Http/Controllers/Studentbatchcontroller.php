<?php

namespace App\Http\Controllers;

use App\Models\Studentbatch;
use App\Services\StudentBatchService;
use App\Http\Requests\StudentBatchRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class StudentBatchcontroller extends Controller
{
    //
    protected StudentBatchService $studentBatchService;
    public function __construct(StudentBatchService $studentBatchService)
    {
        $this->studentBatchService = $studentBatchService;
    }
    public function create_student_batch(StudentBatchRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createStudentBatch = $this->studentBatchService->createStudentBatch($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Batch Created Successfully", $createStudentBatch, null, 201);
    }

    public function update_student_batch(Request $request)
    {
        $batch_id = $request->route('batch_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentBatch = $this->studentBatchService->updateStudentBatch($request->validated(), $batch_id, $currentSchool);
        return ApiResponseService::success('Student Batch Updated Successfully', $updateStudentBatch, null, 200);
    }

    public function delete_student_batch(Request $request)
    {
        $batch_id = $request->route('batch_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentBatches = $this->studentBatchService->deleteStudentBatch($batch_id, $currentSchool);
        return ApiResponseService::success("Student Batch Deleted Successully", $deleteStudentBatches, null, 200);
    }

    public function get_all_student_batches(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentBatches = $this->studentBatchService->getStudentBatches($currentSchool);
        return ApiResponseService::success('Student Batch Fetched Succefully', $getStudentBatches,  null, 200);
    }
}
