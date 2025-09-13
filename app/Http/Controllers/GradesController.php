<?php

namespace App\Http\Controllers;

use App\Http\Requests\Exam\ExamIdRequest;
use App\Http\Requests\Grade\AutoGenExamGradingRequest;
use App\Http\Requests\Grade\BulkConfigureByOtherGradesRequest;
use App\Http\Requests\Grade\BulkCreateGradeRequest;
use App\Http\Requests\Grade\BulkDeleteGradeConfigRequest;
use App\Services\AddGradesService;
use App\Services\ApiResponseService;
use App\Models\Exams;
use App\Models\Examtype;
use App\Services\AutoGenExamGradingService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Grade\CreateGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use App\Services\GradesService;
use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GradesController extends Controller
{

    protected AddGradesService  $addGradesService;
    protected AutoGenExamGradingService $autoGenExamGradingService;
    protected GradesService $gradesService;
    public function __construct(
    AddGradesService $addGradesService,
    GradesService $gradesService,
    AutoGenExamGradingService $autoGenExamGradingService
    )
    {
        $this->addGradesService = $addGradesService;
        $this->gradesService = $gradesService;
        $this->autoGenExamGradingService = $autoGenExamGradingService;
    }

    public function updateExamGrades(UpdateGradeRequest $request){
        try{
          $currentSchool = $request->attributes->get('currentSchool');
          $this->addGradesService->updateExamGrades($request->grades, $currentSchool);
          return ApiResponseService::success("Grades Updated Successfully");
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkCreateExamGrades(BulkCreateGradeRequest $request){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $this->addGradesService->bulkCreateExamGrades($request->validated(), $currentSchool);
           return ApiResponseService::success("Grades Created Successfully", null, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteGradesByGradeConfig(BulkDeleteGradeConfigRequest $request){
         try{
            Log::info("school grades config");
           $currentSchool = $request->attributes->get('currentSchool');
           $this->addGradesService->bulkDeleteGradesConfig($currentSchool, $request->validated());
           return ApiResponseService::success("Grades Deleted Successfully", null, null, 200);
        }
        catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkConfigureByOtherGradeConfig(BulkConfigureByOtherGradesRequest $request){
        try{
           $currentSchool = $request->attributes->get('currentSchool');
           $this->addGradesService->bulkConfigureByOtherGrades($request->validated(), $currentSchool);
           return ApiResponseService::success("Grades Configured Successfully", null, null, 200);
        }
        catch(Throwable $e){
           return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function deleteGradeConfig(Request $request, $configId){
        try{
             $currentSchool = $request->attributes->get('currentSchool');
             $this->addGradesService->deleteGradesConfig($currentSchool, $configId);
             return ApiResponseService::success("School Grades Configuration Deleted Successfully", null, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getGradeConfigDetails(Request $request, $configId){
        try{
          $currentSchool = $request->attributes->get('currentSchool');
          $configDetails = $this->addGradesService->getGradeConfigDetails($currentSchool, $configId);
          return ApiResponseService::success("Grade Configuration Details Fetched Successfully", $configDetails, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function autoGenExamGrading(AutoGenExamGradingRequest $request){
        $examGrading = $this->autoGenExamGradingService->autoGenerateExamGrading($request->validated());
        return ApiResponseService::success("Grading Generated Successfully", $examGrading, null, 200);
    }
    public function createExamGrades(CreateGradeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createGrades = $this->addGradesService->makeGradeForExam($request->grades, $currentSchool);
            return ApiResponseService::success("Exam Grades Created Succefully", $createGrades, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    public function createGradesByOtherGrades(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $configId = $request->route('configId');
        $targetConfigId = $request->route('targetConfigId');
        try {
            $createGrades = $this->addGradesService->configureByOtherGrades($configId, $currentSchool, $targetConfigId);
            return ApiResponseService::success("Exam Grades Added Successfully", $createGrades, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
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
    public function getRelatedExams(Request $request, $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $exam = Exams::where('school_branch_id', $currentSchool->id)
        ->where('id', $examId)
        ->with(['specialty', 'level', 'examType', 'schoolSemester'])
        ->first();

        $examType = $exam->examType;
        if (!$examType || $examType->type == 'exam') {
            $semester = $examType->semester;
            $caExamType = Examtype::where('semester', $semester)
                ->where('type', 'ca')
                ->first();
                if (!$caExamType) {
                    return ApiResponseService::error("exam type not found", null, 404);
                }
            $additionalExams = Exams::where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('semester_id', $exam->semester_id)
            ->where("level_id", $exam->level_id)
            ->with(['examType', 'level', 'specialty', 'schoolSemester'])
            ->get();
            if($additionalExams->isEmpty()){
                return ApiResponseService::error("No related exams found for {$exam->specialty->specialty_name} {$exam->level->level_name}", null, 404);
            }
            return ApiResponseService::success("Related Exams Fetched Sucessfully", [$additionalExams, $exam], null, 200);
        } else {
            return ApiResponseService::error("This is not an exam", null, 400);
        }
    }
}
