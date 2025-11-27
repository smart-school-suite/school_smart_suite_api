<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\ExamIdRequest;
use App\Services\Grade\ExamGradeService;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;

class ExamGradeController extends Controller
{
    protected ExamGradeService $gradesService;
    public function __construct(
    ExamGradeService $gradesService,
    )
    {
        $this->gradesService = $gradesService;
    }

        public function getAllGrades(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getGrades = $this->gradesService->getExamGrades($currentSchool);
        return ApiResponseService::success("Exam Grades Fetched Sucessfully", $getGrades, null, 200);
    }

    public function deleteGrades(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteGrades = $this->gradesService->deleteExamGrading($currentSchool, $examId);
        return ApiResponseService::success("Grade Deleted Sucessfully", $deleteGrades, null, 200);
    }

    public function bulkDeleteGrades(ExamIdRequest $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkDeleteGrades = $this->gradesService->bulkDeleteExamGrading($request->examIds, $currentSchool);
           return ApiResponseService::success("Grades Deleted Successfully", $bulkDeleteGrades, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getGradesConfigByExam(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');

        $gradesByExam = $this->gradesService->getExamGradesConfiguration($currentSchool, $examId);
        return ApiResponseService::success("Grades By Exam Configuration Fetched Successfully", $gradesByExam, null, 200);
    }

    public function getExamConfigData(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getExamConfig = $this->gradesService->getExamConfigData($currentSchool, $examId);
        return ApiResponseService::success("Exam Grades Configuration Fetched Succesfully", $getExamConfig, null, 200);
    }
}
