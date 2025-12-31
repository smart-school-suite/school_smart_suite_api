<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentSource\CreateStudentSourceRequest;
use App\Http\Requests\StudentSource\UpdateStudentSourceRequest;
use App\Services\ApiResponseService;
use App\Services\Student\StudentSourceService;

class StudentSourceController extends Controller
{
    protected StudentSourceService $studentSourceService;
    public function __construct(StudentSourceService $studentSourceService)
    {
        $this->studentSourceService = $studentSourceService;
    }

    public function createStudentSource(CreateStudentSourceRequest $request)
    {
        $createSource = $this->studentSourceService->createStudentSource($request->validated());
        return ApiResponseService::success(
            "Student Source Created",
            $createSource,
            null,
            200
        );
    }
    public function updateStudentSource(UpdateStudentSourceRequest $request, $studentSourceId)
    {
        $updateSource = $this->studentSourceService->updateStudentSource($request->validated, $studentSourceId);
        return ApiResponseService::success(
            "Student Source Updated Successfully",
            $updateSource,
            null,
            200
        );
    }
    public function getAllStudentSource()
    {
        $studentSources = $this->studentSourceService->getAllStudentSource();
        return ApiResponseService::success(
            "Student Source Fetched Successfully",
            $studentSources,
            null,
            200
        );
    }
    public function getActiveStudentSource()
    {
        $studentSources = $this->studentSourceService->getActiveStudentSource();
        return ApiResponseService::success(
            "Student Source Fetched Successfully",
            $studentSources,
            null,
            200
        );
    }
    public function getStudentSourceDetails($studentSourceId)
    {
        $studentSourceDetails = $this->studentSourceService->getStudentSourceById($studentSourceId);
        return ApiResponseService::success(
            "Student Source Details Fetched Successfully",
            $studentSourceDetails,
            null,
            200
        );
    }
    public function deleteStudentSource($studentSourceId)
    {
        $deleteStudentSource = $this->studentSourceService->deleteStudentSource($studentSourceId);
        return ApiResponseService::success(
            "Student Source Deleted",
            $deleteStudentSource,
            null,
            200
        );
    }
    public function activateStudentSource($studentSourceId)
    {
        $activateStudentSource = $this->studentSourceService->activateStudentSource($studentSourceId);
        return ApiResponseService::success(
            "Student Source Activated",
            $activateStudentSource,
            null,
            200
        );
    }
    public function deactivateStudentSource($studentSourceId)
    {
        $deactivateStudentSource = $this->studentSourceService->deactivateStudentSource($studentSourceId);
        return ApiResponseService::success(
            "Student Source Deactivated Successfully",
            $deactivateStudentSource,
            null,
            200
        );
    }
}
