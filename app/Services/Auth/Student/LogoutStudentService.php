<?php

namespace App\Services\Auth\Student;

class LogoutStudentService
{
    public function logoutStudent($request)
    {
        $request->user()->currentAccessToken()->delete();
    }
}
