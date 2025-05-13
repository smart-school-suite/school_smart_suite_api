<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\CreateExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Requests\Exam\BulkUpdateExamRequest;
use App\Http\Resources\AssiocaiteWeigtedMarkLetterGrades;
use App\Http\Requests\ExamGrading\BulkAddExamGradingRequest;
use App\Http\Resources\AccessedExamResource;
use App\Http\Resources\ExamResource;
use App\Services\ExamService;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;

class ExamsController extends Controller
{
    //
    protected ExamService $examService;
    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }
    public function createExam(CreateExamRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createExam = $this->examService->createExam($request->validated(), $currentSchool);
        return ApiResponseService::success("Exam Created Succefully", $createExam, null, 201);
    }
    public function updateExam(UpdateExamRequest $request, $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateExam = $this->examService->updateExam($examId, $currentSchool,  $request->validated());
        return ApiResponseService::success("Exam Updated Successfully", $updateExam, null, 200);
    }
    public function deleteExam(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteExam = $this->examService->deleteExam($examId, $currentSchool);
        return ApiResponseService::success('Exam deleted sucessfully', $deleteExam, null, 200);
    }
    public function getExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getExams = $this->examService->getExams($currentSchool);
        return ApiResponseService::success("Exam Data Fetched Succefully", ExamResource::collection($getExams), null, 200);
    }
    public function getExamDetails(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examDetails = $this->examService->examDetails($currentSchool, $examId,);
        return ApiResponseService::success('Exam Details Fetched Sucessfully', $examDetails, null, 200);
    }
    public function associateWeightedMarkWithLetterGrades(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examData = $this->examService->getAssociateWeightedMarkLetterGrades($examId, $currentSchool);
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
    public function bulkDeleteExam($examIds){
        $idsArray = explode(',', $examIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:exams,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $deleteExam = $this->examService->bulkDeleteExam($idsArray);
           return ApiResponseService::success("Exam Deleted Succesfully", $deleteExam, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkAddExamGrading(BulkAddExamGradingRequest $request) {
       try{
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkAddExamGrading = $this->examService->bulkAddExamGrading($request->exam_grading, $currentSchool);
        return ApiResponseService::success("Exam Grading Added Successfully", $bulkAddExamGrading, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }
    public function bulkUpdateExam(BulkUpdateExamRequest $request){
        try{
            $bulkUpdateExam = $this->examService->bulkUpdateExam($request->exams);
            return ApiResponseService::success("Exam Updated Successfully", $bulkUpdateExam, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
