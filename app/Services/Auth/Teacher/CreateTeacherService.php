<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\SendPasswordMailJob;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
class CreateTeacherService
{
    // Implement your logic here
    public function createInstructor($teacherData, $currentSchool){
        $password = $this->generateRandomPassword();
        $instructor = new Teacher();
        $instructor->name = $teacherData["name"];
        $instructor->email = $teacherData["email"];
        $instructor->password = Hash::make($password);
        $instructor->phone_one = $teacherData["phone_one"];
        $instructor->employment_status = $teacherData["employment_status"];
        $instructor->highest_qualification = $teacherData["highest_qualification"];
        $instructor->field_of_study = $teacherData["field_of_study"];
        $instructor->years_experience = $teacherData["years_experience"];
        $instructor->salary = $teacherData["salary"];
        $instructor->school_branch_id = $currentSchool->id;
        $instructor->save();
        SendPasswordMailJob::dispatch( $password, $teacherData['email']);
        return $instructor;
    }
    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }

}
