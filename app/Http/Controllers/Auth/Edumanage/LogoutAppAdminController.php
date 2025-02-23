<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class LogoutAppAdminController extends Controller
{
    //logouteduadmincontroller
    public function logout_eduadmin(Request $request){
        $request->user()->currentAccessToken()->delete();
        return ApiResponseService::success("Logout Successfull", null, null, 200);
    }
}
