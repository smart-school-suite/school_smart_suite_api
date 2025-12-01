<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\CreateStudentRequest;
use App\Services\ApiResponseService;
use App\Services\Auth\Student\CreateStudentService;


class CreateStudentController extends Controller
{
    //
    protected CreateStudentService $createStudentService;
    public function __construct(CreateStudentService $createStudentService)
    {
        $this->createStudentService = $createStudentService;
    }
    public function createStudent(CreateStudentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $createStudent = $this->createStudentService->createStudent($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Student created successfully", $createStudent, null, 201);
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
