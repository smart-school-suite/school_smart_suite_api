<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\Auth\Student\LogoutStudentService;
use Illuminate\Http\Request;

class LogoutStudentController extends Controller
{
    //
    protected LogoutStudentService $logoutStudentService;
    public function __construct(LogoutStudentService $logoutStudentService){
        $this->logoutStudentService = $logoutStudentService;
    }
    public function logoutStudent(Request $request){
        $this->logoutStudentService->logoutStudent($request);
    }
}
