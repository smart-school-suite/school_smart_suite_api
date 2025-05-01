<?php

namespace App\Http\Controllers\Auth\AppAdmin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AppAdmin\LogoutAppAdminService;
use Illuminate\Http\Request;

class LogoutAppAdminController extends Controller
{
    //logouteduadmincontroller
    protected LogoutAppAdminService $logoutAppAdminService;
    public function __construct(LogoutAppAdminService $logoutAppAdminService)
    {
        $this->logoutAppAdminService = $logoutAppAdminService;
    }
    public function logoutAppAdmin(Request $request){
        $this->logoutAppAdminService->logoutAppAdmin($request);
    }
}
