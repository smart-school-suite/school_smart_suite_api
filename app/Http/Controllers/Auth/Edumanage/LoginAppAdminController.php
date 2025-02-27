<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\Auth\AppAdmin\LoginAppAdminService;
use App\Services\ApiResponseService;

class LoginAppAdminController extends Controller
{
    //logineduadmincontroller

    protected LoginAppAdminService $loginAppAdminService;
    public function __construct(LoginAppAdminService $loginAppAdminService){
         $this->loginAppAdminService = $loginAppAdminService;
    }
    public function loginAppAdmin(LoginRequest $request)
    {
       $loginAppAdmin = $this->loginAppAdminService->loginAppAdmin($request->valiidated());
       return ApiResponseService::success("Login Succesfull", $loginAppAdmin, null, 200);
    }
}
