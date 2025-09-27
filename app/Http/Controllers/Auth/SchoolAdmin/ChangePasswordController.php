<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\Auth\SchoolAdmin\ChangeSchoolAdminPasswordService;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\ApiResponseService;

class ChangePasswordController extends Controller
{
    protected ChangeSchoolAdminPasswordService $changeSchoolAdminPasswordService;
    public function __construct(ChangeSchoolAdminPasswordService $changeSchoolAdminPasswordService){
        $this->changeSchoolAdminPasswordService = $changeSchoolAdminPasswordService;
    }
    public function changeSchoolAdminPassword(ChangePasswordRequest $request){
         $this->changeSchoolAdminPasswordService->changeSchoolAdminPassword($request->validated());
         return ApiResponseService::success("Password changed successfully", null, null, 200);
    }
}
