<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\StatisticalJobs\OperationalJobs\SchoolAdminStatJob;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class CreateSchoolAdminService
{
    // Implement your logic here
    public function createSchoolAdmin($schoolAdminData, $currentSchool){
        $password = $this->generateRandomPassword();
        $schoolAdmin = new Schooladmin();
        $schoolAdminId = Str::uuid()->toString();
        $schoolAdmin->id = $schoolAdminId;
        $schoolAdmin->name = $schoolAdminData["name"];
        $schoolAdmin->email = $schoolAdminData["email"];
        $schoolAdmin->password = Hash::make($password);
        $schoolAdmin->first_name = $schoolAdminData["first_name"];
        $schoolAdmin->last_name = $schoolAdminData["last_name"];
        $schoolAdmin->school_branch_id = $currentSchool->id;
        $schoolAdmin->save();
        $schoolAdmin->assignRole('schoolAdmin');
        SendPasswordVaiMailJob::dispatch( $password, $schoolAdminData['email']);
        SchoolAdminStatJob::dispatch($currentSchool->id, $schoolAdminId);
        return $schoolAdmin;
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
