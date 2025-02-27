<?php

namespace App\Services\Auth\Student;

class GetAuthStudentService
{
    // Implement your logic here
    public function getAuthStudent(){
        $getAuthStudent = auth()->guard('student')->user();
        return $getAuthStudent;
    }
}
