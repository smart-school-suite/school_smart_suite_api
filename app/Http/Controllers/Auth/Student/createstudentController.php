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
        try {
            $createStudent = $this->createStudentService->createStudent($request->validated(), $currentSchool);
            return ApiResponseService::success("Student created successfully", $createStudent, null, 201);
        } catch (\Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
}
