<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\LogoutSchoolAdminService;
use Illuminate\Http\Request;

class LogoutSchoolAdminController extends Controller
{
    //
    protected LogoutSchoolAdminService $logoutSchoolAdminService;
    public function __construct(LogoutSchoolAdminService $logoutSchoolAdminService)
    {
        $this->logoutSchoolAdminService = $logoutSchoolAdminService;
    }
    public function logoutSchoolAdmin(Request $request)
    {
        $this->logoutSchoolAdminService->logoutSchoolAdmin($request);
    }
}
