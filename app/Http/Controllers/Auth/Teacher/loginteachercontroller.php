<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\Teacher\LoginTeacherService;
use App\Http\Requests\LoginRequest;

class LoginTeacherController extends Controller
{
    //
    protected LoginTeacherService $loginTeacherService;
    public function __construct(LoginTeacherService $loginTeacherService){
        $this->loginTeacherService = $loginTeacherService;
    }
    public function loginInstructor(LoginRequest $request){
        $loginTeacher = $this->loginTeacherService->loginTeacher($request->validated());
        return ApiResponseService::success("OTP token sent to email sucessfully", $loginTeacher, null, 200);
    }
}
