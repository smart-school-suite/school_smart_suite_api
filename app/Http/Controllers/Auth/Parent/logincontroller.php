<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use App\Services\Auth\Guardian\LoginParentService;
use App\Http\Requests\LoginRequest;
use App\Services\ApiResponseService;

class logincontroller extends Controller
{
    protected LoginParentService $loginParentService;
    public function __construct(LoginParentService $loginParentService){
        $this->loginParentService = $loginParentService;
    }
    public function loginParent(LoginRequest $request)
    {
       $loginParent = $this->loginParentService->loginParent($request->validated());
       return ApiResponseService::success("Login Parent Success !! OTP token sent sucessfully", $loginParent, null, 200);
    }
}
