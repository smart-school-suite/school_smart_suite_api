<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateExamTypeRequest;
use App\Http\Requests\ExamTypeRequest;
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
    public function create_exam_type(ExamTypeRequest $request)
    {
        $createExamType = $this->examtypeService->createExamType($request->validated());
        return ApiResponseService::success("exam type created succefully", $createExamType, null, 201);
    }

    public function delete_exam_type($exam_id)
    {
        $deleteExamType = $this->examtypeService->deleteExamType($exam_id);
        return ApiResponseService::success("Exam type Deleted Sucessfully", $deleteExamType, null, 200);
    }

    public function update_exam_type(UpdateExamTypeRequest $request, $exam_id)
    {
        $updateExam = $this->examtypeService->updateExamType($request->validated(), $exam_id);
        return ApiResponseService::success("Exam updated succefully", $updateExam, null, 200);
    }

    public function get_all_exam_type(Request $request)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        $examType = $this->examtypeService->getExamType($currentSchool);
        return ApiResponseService::success("Exam records fetched successfully", $examType, null, 200);
    }
}
