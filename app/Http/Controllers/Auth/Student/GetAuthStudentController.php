<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\GetAuthStudentService;
use Illuminate\Http\Request;

class GetAuthStudentController extends Controller
{
    //getauthenticatedstudentcontroller
    protected GetAuthStudentService $getAuthStudentService;
    public function __construct(GetAuthStudentService $getAuthStudentService){
        $this->getAuthStudentService = $getAuthStudentService;
    }
    public function getAuthStudent(Request $request){
        $getAuthStudent = $this->getAuthStudentService->getAuthStudent();
        return ApiResponseService::success("Authenticated Student", $getAuthStudent, null, 200);
    }
}
