<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAcademicYear\CreateSchoolAcademicYearRequest;
use App\Http\Requests\SchoolAcademicYear\UpdateSchoolAcademicYearRequest;
use Illuminate\Http\Request;
use App\Services\AcademicYear\SchoolAcademicYearService;
use App\Services\ApiResponseService;

class SchoolAcademicYearController extends Controller
{
    protected SchoolAcademicYearService $schoolAcademicYearService;

    public function __construct(SchoolAcademicYearService $schoolAcademicYearService)
    {
        $this->schoolAcademicYearService = $schoolAcademicYearService;
    }

    public function createSchoolAcademicYear(CreateSchoolAcademicYearRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createSchoolAcademicYear = $this->schoolAcademicYearService->createSchoolAcademicYear($request->validated(), $currentSchool);
        return ApiResponseService::success($createSchoolAcademicYear, 'School Academic Year created successfully.');
    }
    public function updateSchoolAcademicYear(UpdateSchoolAcademicYearRequest $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSchoolAcademicYear = $this->schoolAcademicYearService->updateSchoolAcademicYear($currentSchool, $schoolAcademicYearId, $request->all());
        return ApiResponseService::success($updateSchoolAcademicYear, 'School Academic Year updated successfully.');
    }
    public function getSchoolAcademicYears(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAcademicYears = $this->schoolAcademicYearService->getSchoolAcademicYears($currentSchool);
        return ApiResponseService::success($schoolAcademicYears, 'School Academic Years retrieved successfully.');
    }
    public function getSchoolAcademicYearById(Request $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAcademicYear = $this->schoolAcademicYearService->getSchoolAcademicYearById($currentSchool, $schoolAcademicYearId);
        return ApiResponseService::success($schoolAcademicYear, 'School Academic Year retrieved successfully.');
    }
    public function deleteSchoolAcademicYear(Request $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deletedSchoolAcademicYear = $this->schoolAcademicYearService->deleteSchoolAcademicYear($currentSchool, $schoolAcademicYearId);
        return ApiResponseService::success($deletedSchoolAcademicYear, 'School Academic Year deleted successfully.');
    }
}
