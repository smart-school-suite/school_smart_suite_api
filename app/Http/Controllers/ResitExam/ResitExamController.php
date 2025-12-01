<?php

namespace App\Http\Controllers\ResitExam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ResitExam\ResitExamIdRequest;
use App\Http\Requests\ResitExam\UpdateResitExamRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\ExamGrading\BulkAddResitExamGradingRequest;
use App\Http\Requests\Exam\BulkUpdateResitExamRequest;
use App\Http\Resources\ResitExamResource;
use App\Services\ResitExam\ResitExamService;
use Exception;

class ResitExamController extends Controller
{
    protected ResitExamService $resitExamService;
    public function __construct(ResitExamService $resitExamService)
    {
        $this->resitExamService = $resitExamService;
    }
    public function updateResitExam(UpdateResitExamRequest $request, $resitExamId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateResitExam = $this->resitExamService->updateResitExam($request->validated(), $resitExamId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Resit Exam Updated Successfully", $updateResitExam, null, 200);
    }
    public function getAllResitExams(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $resitExams = $this->resitExamService->getAllResitExams($currentSchool);
        return ApiResponseService::success("Resit Exams Fetched Successfully", ResitExamResource::collection($resitExams), null, 200);
    }
    public function deleteResitExam(Request $request, $resitExamId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteResitExam = $this->resitExamService->deleteResitExam($resitExamId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Resit Exam Deleted Successfully", $deleteResitExam, null, 200);
    }
    public  function addResitExamGrading(Request $request)
    {
        try {
            $resitExamId = $request->route('resitExamId');
            $gradesConfigId = $request->route('gradesConfigId');
            $authAdmin = $this->resolveUser();
            $currentSchool = $request->attributes->get('currentSchool');
            $addGrading = $this->resitExamService->addExamGrading($resitExamId, $currentSchool, $gradesConfigId, $authAdmin);
            return ApiResponseService::success("Resit Exam Grading Added Successfully", $addGrading, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getResitExamDetails(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExamDetails = $this->resitExamService->examDetails($currentSchool, $resitExamId);
        return ApiResponseService::success("Resit Exam Details Fetched Successfully", $resitExamDetails, null, 200);
    }
    public function bulkDeleteResitExam(ResitExamIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteExam = $this->resitExamService->bulkDeleteResitExam($request->resitExamIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Deleted Succesfully", $deleteExam, null, 200);
    }
    public function bulkAddExamGrading(BulkAddResitExamGradingRequest $request)
    {

        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkAddExamGrading = $this->resitExamService->bulkAddExamGrading($request->exam_grading, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Grading Added Successfully", $bulkAddExamGrading, null, 200);
    }
    public function bulkUpdateResitExam(BulkUpdateResitExamRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkUpdateExam = $this->resitExamService->bulkUpdateResitExam($request->exams, $currentSchool, $authAdmin);
        return ApiResponseService::success("Exam Updated Successfully", $bulkUpdateExam, null, 200);
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
