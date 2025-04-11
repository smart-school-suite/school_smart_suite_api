<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
class CreateSchoolAdminService
{
    // Implement your logic here
    public function createSchoolAdmin($schoolAdminData, $currentSchool){
        $password = $this->generateRandomPassword();
        $schoolAdmin = new Schooladmin();
        $schoolAdmin->name = $schoolAdminData["name"];
        $schoolAdmin->email = $schoolAdminData["email"];
        $schoolAdmin->password = Hash::make($password);
        $schoolAdmin->first_name = $schoolAdminData["first_name"];
        $schoolAdmin->last_name = $schoolAdminData["last_name"];
        $schoolAdmin->school_branch_id = $currentSchool->id;
        $schoolAdmin->save();
        $schoolAdmin->assignRole('schoolAdmin');
        return $schoolAdmin;
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
