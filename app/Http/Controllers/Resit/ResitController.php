<?php

namespace App\Http\Controllers\Resit;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentResit\StudentResitIdRequest;
use App\Http\Resources\ResitResource;
use Exception;
use Illuminate\Http\Request;
use App\Services\Resit\ResitService;
use App\Http\Requests\StudentResit\BulkUpdateStudentResitRequest;
use App\Services\ApiResponseService;

class ResitController extends Controller
{
    protected ResitService $studentResitService;


    public function __construct(
        ResitService $studentResitService,
    ) {
        $this->studentResitService = $studentResitService;
    }

    public function updateResit(Request $request, $resitId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateStudentResit = $this->studentResitService->updateStudentResit($request->all(), $currentSchool, $resitId);
        return ApiResponseService::success("Resit Entry Updated Successfully", $updateStudentResit, null, 200);
    }
    public function deleteResit(Request $request, $resitId)
    {
        $resitId = $request->route('resitId');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteStudentResit = $this->studentResitService->deleteStudentResit($resitId, $currentSchool);
        return ApiResponseService::success("Student Resit Record Not Found", $deleteStudentResit, null, 200);
    }
    public function getResitByStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $studentId = $request->route('studentId');
        $getMyResits = $this->studentResitService->getMyResits($currentSchool, $studentId);
        return ApiResponseService::success("Student Records Fetched Sucessfully", $getMyResits, null, 200);
    }
    public function getAllResits(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getStudentResits = $this->studentResitService->getResitableCourses($currentSchool);
        return ApiResponseService::success("Student Resit Records Fetched Sucessfully", ResitResource::collection($getStudentResits), null, 200);
    }
    public function getResitDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitId = $request->route("resitId");
        $getStudentResitDetails = $this->studentResitService->getStudentResitDetails($currentSchool, $resitId);
        return ApiResponseService::success("Student Resit Details Fetched Successfully", $getStudentResitDetails, null, 200);
    }

    public function getPreparedResitEvaluationData(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $resitExamId = $request->route('resitExamId');
        $candidateId = $request->route('candidateId');
        $prepareResitData = $this->studentResitService->getResitEvaluationHelperData($currentSchool, $resitExamId, $candidateId);
        return ApiResponseService::success("Resit Evaluation Helper Data Fetched Successfully", $prepareResitData, null, 200);
    }

    public function getResitScoresByCandidate(Request $request)
    {
        try {
            $candidateId = $request->route('candidateId');
            $currentSchool = $request->attributes->get("currentSchool");
            $resitScores = $this->studentResitService->getResitScoresByCandidate($currentSchool, $candidateId);
            return ApiResponseService::success("Resit Scores Fetched Successfully", $resitScores, null, 200);
        } catch (Exception $e) {
            return  ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteStudentResit(StudentResitIdRequest $request)
    {
        try {
            $bulkDeleteStudentResit = $this->studentResitService->bulkDeleteStudentResit($request->resitIds);
            return ApiResponseService::success("Student Resit Deleted Succesfully", $bulkDeleteStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateStudentResit(BulkUpdateStudentResitRequest $request)
    {
        try {
            $bulkUpdateStudentResit = $this->studentResitService->bulkUpdateStudentResit($request->validated);
            return ApiResponseService::success("Student Resit Updated Successfully", $bulkUpdateStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getAllEligableStudentByExam(Request $request, $resitExamId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $eligableStudents = $this->studentResitService->getAllEligableStudents($currentSchool, $resitExamId);
        return ApiResponseService::success("Eligable Students Fetched Successfully", $eligableStudents, null, 200);
    }
    public function getEligableResitExamByStudent(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitExams = $this->studentResitService->getEligableResitExamByStudent($currentSchool, $studentId);
        return ApiResponseService::success("Resit Exams Fetched Successfully", $getResitExams, null, 200);
    }

    public function getResitStudentId(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResits = $this->studentResitService->getResitStudentId($currentSchool, $studentId);
        return ApiResponseService::success("Student Resits Fetched Successfully", $studentResits, null, 200);
    }

    public function getResitStudentIdSemesterId(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentId = $request->route("studentId");
        $semesterId = $request->route("semesterId");
        $studentResits = $this->studentResitService->getResitStudentIdSemesterId($currentSchool, $studentId, $semesterId);
        return ApiResponseService::success("Student Resits Fetched Successfully", $studentResits, null, 200);
    }

    public function getResitStudentIdCarryOver(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResits = $this->studentResitService->getResitStudentIdCarryOver($currentSchool, $studentId);
        return ApiResponseService::success("Student Carry Overs Fetched Succesfully", $studentResits, null, 200);
    }
}
