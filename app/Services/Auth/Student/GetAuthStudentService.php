<?php

namespace App\Services\Auth\Student;
use App\Exceptions\AppException;
use App\Models\Student;
class GetAuthStudentService
{
    // Implement your logic here
    public function getAuthStudent(){
        $authStudent = auth()->guard('student')->user();
        if(!$authStudent){
              throw new AppException(
                  "Student UnAuthenticated",
                  401,
                  "Student UnAuthenticated",
                  "Failed to get authenticated student please try logging again"
              );
        }
        $student = Student::where("school_branch_id", $authStudent->school_branch_id)
                           ->with(['tuitionFees', 'specialty', 'level', 'department'])
                            ->find($authStudent->id);

        return $student;
    }
}
