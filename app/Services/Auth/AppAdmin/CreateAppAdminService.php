<?php

namespace App\Services\Auth\AppAdmin;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
class CreateAppAdminService
{
    // Implement your logic here
    public function createAppAdmin($adminData){
        $new_school_admin_instance = new Edumanageadmin();
        $new_school_admin_instance->name = $adminData["name"];
        $new_school_admin_instance->email = $adminData["email"];
        $new_school_admin_instance->phone_number = $adminData["phone_number"];
        $new_school_admin_instance->password = Hash::make($adminData["password"]);
        $new_school_admin_instance->save();
        return $new_school_admin_instance;
    }
}
