<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Services\Auth\Student\ChangeStudentPasswordService;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    protected ChangeStudentPasswordService $changeStudentPasswordService;
    public function __construct(ChangeStudentPasswordService $changeStudentPasswordService)
    {
        $this->changeStudentPasswordService = $changeStudentPasswordService;
    }
    public function changeStudentPassword(ChangePasswordRequest $request)
    {
        $this->changeStudentPasswordService->changeStudentPassword($request->validated());
    }
}
