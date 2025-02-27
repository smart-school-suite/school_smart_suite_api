<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Auth\Guardian\GetAuthGuardianService;

class GetAuthParentController extends Controller
{
    //getauthenticatedparentcontroller
    protected GetAuthGuardianService $getAuthGuardianService;
    public function __construct(GetAuthGuardianService $getAuthGuardianService){
        $this->getAuthGuardianService = $getAuthGuardianService;
    }
    public function getAuthParent(Request $request){
        $getAuthGuardian = $this->getAuthGuardianService->getAuthGuardian();
        return ApiResponseService::success("Authenticated Parent Fetched Succesfully", $getAuthGuardian, null, 200);
    }
}
