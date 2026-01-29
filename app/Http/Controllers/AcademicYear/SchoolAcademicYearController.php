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
        return ApiResponseService::success( 'School Academic Year created successfully.', $createSchoolAcademicYear, null, 201);
    }
    public function updateSchoolAcademicYear(UpdateSchoolAcademicYearRequest $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateSchoolAcademicYear = $this->schoolAcademicYearService->updateSchoolAcademicYear($currentSchool, $schoolAcademicYearId, $request->all());
        return ApiResponseService::success( 'School Academic Year updated successfully.', $updateSchoolAcademicYear, null, 200);
    }
    public function getSchoolAcademicYears(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAcademicYears = $this->schoolAcademicYearService->getSchoolAcademicYears($currentSchool);
        return ApiResponseService::success( 'School Academic Years retrieved successfully.', $schoolAcademicYears, null, 200);
    }
    public function getSchoolAcademicYearById(Request $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAcademicYear = $this->schoolAcademicYearService->getSchoolAcademicYearById($currentSchool, $schoolAcademicYearId);
        return ApiResponseService::success( 'School Academic Year retrieved successfully.', $schoolAcademicYear, null, 200);
    }
    public function deleteSchoolAcademicYear(Request $request, string $schoolAcademicYearId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deletedSchoolAcademicYear = $this->schoolAcademicYearService->deleteSchoolAcademicYear($currentSchool, $schoolAcademicYearId);
        return ApiResponseService::success( 'School Academic Year deleted successfully.', $deletedSchoolAcademicYear, null, 200);
    }
}
