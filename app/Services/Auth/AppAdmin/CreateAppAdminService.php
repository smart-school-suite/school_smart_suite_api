<?php

namespace App\Services\Auth\AppAdmin;
use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\SendPasswordMailJob;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
class CreateAppAdminService
{
    // Implement your logic here
    public function createAppAdmin($adminData){
        $password = $this->generateRandomPassword();
        $appAdmin = new Edumanageadmin();
        $appAdmin->name = $adminData["name"];
        $appAdmin->email = $adminData["email"];
        $appAdmin->phone_number = $adminData["phone_number"];
        $appAdmin->password = Hash::make($password);
        $appAdmin->save();
        SendPasswordVaiMailJob::dispatch($adminData['email'], $password);
        return $appAdmin;
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
