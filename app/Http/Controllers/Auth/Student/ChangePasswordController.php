<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\ChangeStudentPasswordService;

class ChangePasswordController extends Controller
{
    protected ChangeStudentPasswordService $changeStudentPasswordService;

    public function __construct(ChangeStudentPasswordService $changeStudentPasswordService)
    {
        $this->changeStudentPasswordService = $changeStudentPasswordService;
    }

    public function changeStudentPassword(ChangePasswordRequest $request)
    {
        $this->changeStudentPasswordService->changeStudentPassword($request->validated());
        return ApiResponseService::success("Password changed successfully", null, null, 200);
    }
}
