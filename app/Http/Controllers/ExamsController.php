<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\CreateExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Http\Requests\Exam\BulkUpdateExamRequest;
use App\Http\Requests\Exam\ExamIdRequest;
use App\Http\Resources\AssiocaiteWeigtedMarkLetterGrades;
use App\Http\Requests\ExamGrading\BulkAddExamGradingRequest;
use App\Http\Resources\AccessedExamResource;
use App\Http\Resources\ExamResource;
use App\Services\ExamService;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        return ApiResponseService::success('Data fetched Sucessfully', $examData, null, 200);
    }
    public function getAccessedExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student_id = $request->route("student_id");
        $AccessedExams = $this->examService->getAccessExams($student_id, $currentSchool);
        return ApiResponseService::success("Accessed Exams Fetched Sucessfully", AccessedExamResource::collection($AccessedExams), null, 200);
    }
    public function addExamGrading(Request $request, string $gradesConfigId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route("examId");
        $gradesConfigId = $request->route("gradesConfigId");
        $addGradesConfig = $this->examService->addExamGrading($examId, $currentSchool, $gradesConfigId);
        return ApiResponseService::success("Exam Grading Added Successfully", $addGradesConfig, null, 201);
    }
    public function getResitExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examTypeResit = $this->examService->getResitExams($currentSchool);
        return ApiResponseService::success("Exam Type Resit Fetched Successfully", ExamResource::collection($examTypeResit), null, 200);
    }
    public function bulkDeleteExam(ExamIdRequest $request)
    {

        $currentSchool = $request->attributes->get('currentSchool');
        $deleteExam = $this->examService->bulkDeleteExam($request->examIds, $currentSchool);
        return ApiResponseService::success("Exam Deleted Succesfully", $deleteExam, null, 200);
    }
    public function bulkAddExamGrading(BulkAddExamGradingRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkAddExamGrading = $this->examService->bulkAddExamGrading($request->exam_grading, $currentSchool);
        return ApiResponseService::success("Exam Grading Added Successfully", $bulkAddExamGrading, null, 200);
    }
    public function bulkUpdateExam(BulkUpdateExamRequest $request)
    {
        try {
            $bulkUpdateExam = $this->examService->bulkUpdateExam($request->exams);
            return ApiResponseService::success("Exam Updated Successfully", $bulkUpdateExam, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getAllExamsByStudentId(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $exams = $this->examService->getAllExamsByStudentId($currentSchool, $studentId);
        return ApiResponseService::success("Exams Fetched Successfully", $exams, null, 200);
    }

    public function getAllExamsByStudentIdSemesterId(Request $request)
    {
        $studentId = $request->route("studentId");
        $semesterId = $request->route("semesterId");
        $currentSchool = $request->attributes->get('currentSchool');
        $exams = $this->examService->getExamsByStudentIdSemesterId($currentSchool, $studentId, $semesterId);
        return ApiResponseService::success("Exams Fetched Successfully", $exams, null, 200);
    }

    public function getExamGradeScale(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $examGradeScale = $this->examService->getExamGradeScale($examId, $currentSchool);
        return ApiResponseService::success("Exam Grade Scale Fetched Successfully", $examGradeScale, null, 200);
    }

    public function getStudentUpcomingExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $upcomingExams = $this->examService->getUpcomingExams($currentSchool, $authStudent);
        return ApiResponseService::success("Upcoming Exams Fetched Successfully", $upcomingExams, null, 200);
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
