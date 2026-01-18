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

    public function updateExamGrades(UpdateGradeRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->addGradesService->updateExamGrades($request->grades, $currentSchool, $authAdmin);
        return ApiResponseService::success("Grades Updated Successfully");
    }

    public function bulkCreateExamGrades(BulkCreateGradeRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->addGradesService->bulkCreateExamGrades($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Grades Created Successfully", null, null, 200);
    }

    public function bulkDeleteGradesByGradeConfig(BulkDeleteGradeConfigRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->addGradesService->bulkDeleteGradesConfig($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Grades Deleted Successfully", null, null, 200);
    }

    public function bulkConfigureByOtherGradeConfig(BulkConfigureByOtherGradesRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->addGradesService->bulkConfigureByOtherGrades($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Grades Configured Successfully", null, null, 200);
    }
    public function deleteGradeConfig(Request $request, $configId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $this->addGradesService->deleteGradesConfig($currentSchool, $configId, $authAdmin);
        return ApiResponseService::success("School Grades Configuration Deleted Successfully", null, null, 200);
    }

    public function getGradeConfigDetails(Request $request, $configId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $configDetails = $this->addGradesService->getGradeConfigDetails($currentSchool, $configId);
        return ApiResponseService::success("Grade Configuration Details Fetched Successfully", $configDetails, null, 200);
    }
    public function autoGenExamGrading(AutoGenExamGradingRequest $request)
    {
        $examGrading = $this->autoGenExamGradingService->autoGenerateExamGrading($request->validated());
        return ApiResponseService::success("Grading Generated Successfully", $examGrading, null, 200);
    }
    public function createExamGrades(CreateGradeRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $createGrades = $this->addGradesService->makeGradeForExam($request->grades, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Grades Created Succefully", $createGrades, null, 201);
    }

    public function createGradesByOtherGrades(Request $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $configId = $request->route('configId');
        $targetConfigId = $request->route('targetConfigId');
        $createGrades = $this->addGradesService->configureByOtherGrades($configId, $currentSchool, $targetConfigId, $authAdmin);
        return ApiResponseService::success("Exam Grades Added Successfully", $createGrades, null, 201);
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
