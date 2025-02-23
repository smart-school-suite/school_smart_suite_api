<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class LogoutStudentController extends Controller
{
    //
    public function logout_student(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
