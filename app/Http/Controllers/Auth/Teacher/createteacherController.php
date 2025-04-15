<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeacherRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Teacher\CreateTeacherService;

class CreateteacherController extends Controller
{
    //
    protected CreateTeacherService $createTeacherService;
    public function __construct(CreateTeacherService $createTeacherService){
        $this->createTeacherService = $createTeacherService;
    }
    public function createInstructor(CreateTeacherRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createInstructor = $this->createTeacherService->createInstructor($request->validated(), $currentSchool);
        return ApiResponseService::success("Instructor Created Succesfully", $createInstructor, null, 200);
    }
}
