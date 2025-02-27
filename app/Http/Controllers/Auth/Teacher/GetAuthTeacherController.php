<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Auth\Teacher\GetAuthTeacherService;
use Illuminate\Http\Request;

class GetAuthTeacherController extends Controller
{
    //GetAuthTeacherController
    protected GetAuthTeacherService $getAuthTeacherService;
    public function __construct(GetAuthTeacherService $getAuthTeacherService){
        $this->getAuthTeacherService = $getAuthTeacherService;
    }
    public function getAuthTeacher(Request $request){
        $getAuthInstructor = $this->getAuthTeacherService->getAuthTeacher();
        return ApiResponseService::success("Authenticated Teacher fetched Succesfully", $getAuthInstructor, null, 200);
    }
}
