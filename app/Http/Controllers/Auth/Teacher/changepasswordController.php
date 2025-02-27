<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Auth\Teacher\ChangeTeacherPasswordService;
use App\Http\Requests\ChangePasswordRequest;

class ChangePasswordController extends Controller
{
    protected ChangeTeacherPasswordService $changeTeacherPasswordService;
    public function __construct(ChangeTeacherPasswordService $changeTeacherPasswordService)
    {
        $this->changeTeacherPasswordService = $changeTeacherPasswordService;
    }
    public function changeInstructorPassword(ChangePasswordRequest $request)
    {
        $this->changeTeacherPasswordService->changeInstructorPassword($request->validated());
    }
}
