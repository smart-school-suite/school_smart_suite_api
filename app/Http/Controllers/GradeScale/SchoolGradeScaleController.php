<?php

namespace App\Http\Controllers\GradeScale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Grade\AutoGenExamGradingRequest;
use App\Http\Requests\Grade\BulkConfigureByOtherGradesRequest;
use App\Http\Requests\Grade\BulkCreateGradeRequest;
use App\Http\Requests\Grade\BulkDeleteGradeConfigRequest;
use App\Services\Grade\GradeScaleService;
use App\Services\ApiResponseService;
use App\Services\Grade\AutoGenExamGradeScaleService;
use App\Http\Requests\Grade\CreateGradeRequest;
use App\Http\Requests\Grade\UpdateGradeRequest;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;

class SchoolGradeScaleController extends Controller
{
    protected GradeScaleService  $addGradesService;
    protected AutoGenExamGradeScaleService $autoGenExamGradingService;
    public function __construct(
        GradeScaleService $addGradesService,
        AutoGenExamGradeScaleService $autoGenExamGradingService
    ) {
        $this->addGradesService = $addGradesService;
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



}
