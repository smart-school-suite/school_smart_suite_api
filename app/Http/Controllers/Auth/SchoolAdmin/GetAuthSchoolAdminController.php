<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\SchoolAdmin\GetAuthSchoolAdminService;
use Illuminate\Http\Request;

class GetAuthSchoolAdminController extends Controller
{
    //
    //getauthenticatedschoolcontroller
    protected GetAuthSchoolAdminService $getAuthSchoolAdminService;
    public function __construct(){
        $this->getAuthSchoolAdminService = new GetAuthSchoolAdminService();
    }
    public function getAuthSchoolAdmin(Request $request){
        $getAuthSchoolAdmin = $this->getAuthSchoolAdminService->getAuthSchoolAdmin();
        return ApiResponseService::success("Auth School Admin User Fetched Sucessfully", $getAuthSchoolAdmin, null, 200);
    }
}
