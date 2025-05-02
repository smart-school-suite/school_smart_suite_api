<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AppAdmin\LoginAppAdminService;
use App\Http\Requests\Auth\LoginStudentRequest;
use App\Services\ApiResponseService;

class LoginAppAdminController extends Controller
{
    //logineduadmincontroller

    protected LoginAppAdminService $loginAppAdminService;
    public function __construct(LoginAppAdminService $loginAppAdminService){
         $this->loginAppAdminService = $loginAppAdminService;
    }
    public function loginAppAdmin(LoginStudentRequest $request)
    {
       $loginAppAdmin = $this->loginAppAdminService->loginAppAdmin($request->valiidated());
       return ApiResponseService::success("Login Succesfull", $loginAppAdmin, null, 200);
    }
}
