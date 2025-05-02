<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamTypeRequest;
use App\Http\Requests\ExamType\CreateExamTypeRequest;
use App\Http\Requests\ExamType\BulkUpdateExamTypeRequest;
use App\Http\Requests\ExamType\UpdateExamTypeRequest;
use App\Services\ApiResponseService;
use App\Services\ExamTypeService;
use Illuminate\Http\Request;

class ExamTypecontroller extends Controller
{
    //
    protected ExamTypeService $examtypeService;
    public function __construct(ExamtypeService $examtypeService)
    {
        $this->examtypeService = $examtypeService;
    }
    public function createExamType(CreateExamTypeRequest $request)
    {
        $createExamType = $this->examtypeService->createExamType($request->validated());
        return ApiResponseService::success("exam type created succefully", $createExamType, null, 201);
    }

    public function deleteExamType($exam_id)
    {
        $deleteExamType = $this->examtypeService->deleteExamType($exam_id);
        return ApiResponseService::success("Exam type Deleted Sucessfully", $deleteExamType, null, 200);
    }

    public function updateExamType(UpdateExamTypeRequest $request, $exam_id)
    {
        $updateExam = $this->examtypeService->updateExamType($request->validated(), $exam_id);
        return ApiResponseService::success("Exam updated succefully", $updateExam, null, 200);
    }

    public function getExamType(Request $request)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        $examType = $this->examtypeService->getExamType($currentSchool);
        return ApiResponseService::success("Exam records fetched successfully", $examType, null, 200);
    }
}
