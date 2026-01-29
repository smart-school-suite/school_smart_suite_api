<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AcademicYear\SystemAcademicYearService;
use App\Services\ApiResponseService;

class SystemAcademicYearController extends Controller
{
    protected SystemAcademicYearService $systemAcademicYearService;

    public function __construct(SystemAcademicYearService $systemAcademicYearService)
    {
        $this->systemAcademicYearService = $systemAcademicYearService;
    }

    public function getAllSystemAcademicYears(Request $request)
    {
        $systemAcademicYears = $this->systemAcademicYearService->getAllSystemAcademicYears();
        return ApiResponseService::success( 'System Academic Years retrieved successfully.', $systemAcademicYears, null, 200);
    }
    public function getSystemAcademicYearByCurrentYear(Request $request)
    {
        $systemAcademicYears = $this->systemAcademicYearService->getSystemAcademicYearByCurrentYear();
        return ApiResponseService::success( 'System Academic Years for current year retrieved successfully.', $systemAcademicYears, null, 200);
    }
}
