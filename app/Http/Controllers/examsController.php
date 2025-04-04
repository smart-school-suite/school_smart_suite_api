<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssiocaiteWeigtedMarkLetterGrades;
use App\Http\Requests\ExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Resources\AccessedExamResource;
use App\Http\Resources\ExamResource;
use App\Services\ExamService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class ExamsController extends Controller
{
    //
    protected ExamService $examService;
    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }
    public function createExam(ExamRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createExam = $this->examService->createExam($request->validated(), $currentSchool);
        return ApiResponseService::success("Exam Created Succefully", $createExam, null, 201);
    }
    public function updateExam(UpdateExamRequest $request, $exam_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateExam = $this->examService->updateExam($exam_id, $currentSchool,  $request->validated());
        return ApiResponseService::success("Exam Updated Successfully", $updateExam, null, 200);
    }
    public function deleteExam(Request $request, $exam_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteExam = $this->examService->deleteExam($exam_id, $currentSchool);
        return ApiResponseService::success('Exam deleted sucessfully', $deleteExam, null, 200);
    }

    public function getExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getExams = $this->examService->getExams($currentSchool);
        return ApiResponseService::success("Exam Data Fetched Succefully", ExamResource::collection($getExams), null, 200);
    }

    public function getExamDetails(Request $request, string $exam_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examDetails = $this->examService->examDetails($currentSchool, $exam_id,);
        return ApiResponseService::success('Exam Details Fetched Sucessfully', $examDetails, null, 200);
    }

    public function associateWeightedMarkWithLetterGrades(Request $request, string $exam_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examData = $this->examService->getAssociateWeightedMarkLetterGrades($exam_id, $currentSchool);
        return ApiResponseService::success('Data fetched Sucessfully', AssiocaiteWeigtedMarkLetterGrades::collection($examData), null, 200);
    }

    public function getAccessedExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route("student_id");
        $AccessedExams = $this->examService->getAccessExams($student_id, $currentSchool);
        return ApiResponseService::success("Accessed Exams Fetched Sucessfully", AccessedExamResource::collection($AccessedExams), null, 200);
    }

    public function addExamGrading(Request $request, string $gradesConfigId){
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route("examId");
        $gradesConfigId = $request->route("gradesConfigId");
        $addGradesConfig = $this->examService->addExamGrading($examId, $currentSchool, $gradesConfigId);
        return ApiResponseService::success("Exam Grading Added Successfully", $addGradesConfig, null, 201);
    }

    public function getResitExams(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $examTypeResit = $this->examService->getResitExams($currentSchool);
        return ApiResponseService::success("Exam Type Resit Fetched Successfully", ExamResource::collection($examTypeResit), null, 200);
    }
}
