<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Auth\Teacher\LogoutTeacherService;
use Illuminate\Http\Request;

class LogoutTeacherController extends Controller
{
    //
    protected LogoutTeacherService $logoutTeacherService;
    public function __construct(LogoutTeacherService $logoutTeacherService){
        $this->logoutTeacherService = $logoutTeacherService;
    }
    public function logoutInstructor(Request $request){
       $this->logoutTeacherService->logoutAdmin($request);
    }
}
