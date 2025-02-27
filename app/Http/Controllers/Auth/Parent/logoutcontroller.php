<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\Guardian\LogoutGuardianService;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    protected LogoutGuardianService $logoutGuardianService;
    public function __construct(LogoutGuardianService $logoutGuardianService){
        $this->logoutGuardianService = $logoutGuardianService;
    }
    public function logoutParent(Request $request){
        $this->logoutGuardianService->logoutGuardian($request);
    }
}
