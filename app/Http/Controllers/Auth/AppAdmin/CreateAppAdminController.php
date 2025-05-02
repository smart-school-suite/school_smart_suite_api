<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AppAdmin\CreateAppAdminService;
use App\Http\Requests\AppAdmin\CreateAppAdminRequest;
use App\Services\ApiResponseService;

class CreateAppAdminController extends Controller
{
    //createeduadmincontroller
    protected CreateAppAdminService $createAppAdminService;
    public function __construct(CreateAppAdminService $createAppAdminService)
    {
        $this->createAppAdminService = $createAppAdminService;
    }

    public function createAppAdmin(CreateAppAdminRequest $request){

        $createAppAdmin = $this->createAppAdminService->createAppAdmin($request->validated());
        return ApiResponseService::success("App Admin Created Sucessfully", $createAppAdmin, null, 200);
    }
}
