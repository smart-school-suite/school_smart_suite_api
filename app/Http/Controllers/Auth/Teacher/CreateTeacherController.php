<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\CreateTeacherRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Teacher\CreateTeacherService;

class CreateTeacherController extends Controller
{
    //
    protected CreateTeacherService $createTeacherService;
    public function __construct(CreateTeacherService $createTeacherService)
    {
        $this->createTeacherService = $createTeacherService;
    }
    public function createInstructor(CreateTeacherRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $createInstructor = $this->createTeacherService->createInstructor($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Instructor Created Succesfully", $createInstructor, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
