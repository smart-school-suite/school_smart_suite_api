<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\AppAdmin\GetAuthAdminService;
use Illuminate\Http\Request;

class GetAuthAppAdminController extends Controller
{
    //getauthenticatededumanageadmincontroller

    protected GetAuthAdminService $getAuthAdminService;
    public function __construct(GetAuthAdminService $getAuthAdminService){
        $this->getAuthAdminService = $getAuthAdminService;
    }
    public function getAuthAppAdmin(Request $request){
       $getGetAuthAppAdmin = $this->getAuthAdminService->getAuthAppAdmin();
       return ApiResponseService::success("Authenticated App Admin Fetched Succesfully", $getGetAuthAppAdmin, null, 200);
    }
}
