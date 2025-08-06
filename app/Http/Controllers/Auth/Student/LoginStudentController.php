<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\LoginStudentService;
use App\Http\Requests\Auth\LoginStudentRequest;

class LoginStudentController extends Controller
{
    //
    protected LoginStudentService $loginStudentService;
    public function __construct(LoginStudentService $loginStudentService)
    {
        $this->loginStudentService = $loginStudentService;
    }
    public function loginStudent(LoginStudentRequest $request)
    {
        $loginStudent = $this->loginStudentService->loginStudent($request->validated());
        return ApiResponseService::success("OTP sent to your email", $loginStudent, null, 200);
    }
}
