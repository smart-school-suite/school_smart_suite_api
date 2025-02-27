<?php

namespace App\Services\Auth\Teacher;

class GetAuthTeacherService
{
    // Implement your logic here
    public function getAuthTeacher(){
        $authTeacher = auth()->guard('teacher')->user();
        return $authTeacher;
    }
}
