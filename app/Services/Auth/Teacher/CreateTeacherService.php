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
        $instructor->first_name = $teacherData['first_name'];
        $instructor->last_name = $teacherData['last_name'];
        $instructor->password = Hash::make($password);
        $instructor->phone_one = $teacherData["phone_one"];
        $instructor->phone_one = $teacherData["phone_two"] ?? null;
        $instructor->address = $teacherData['address'] ?? null;
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
