<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class LogoutSchoolAdminController extends Controller
{
    //
    public function logout_school_admin(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
