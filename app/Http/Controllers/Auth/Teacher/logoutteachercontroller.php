<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class logoutteachercontroller extends Controller
{
    //
    public function logout_teacher(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
