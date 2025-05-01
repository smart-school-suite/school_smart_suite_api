<?php

namespace App\Services\Auth\AppAdmin;

use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeAppAdminPasswordService
{
    // Implement your logic here

    public function changeAppAdminPassword($passwordData){

        $authAppAdmin = auth()->guard('edumanageadmin')->user();

        if (!$this->checkCurrentPassword($authAppAdmin, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }



        if ($this->updatePassword($authAppAdmin, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }
    }

    protected function checkCurrentPassword($authAppAdmin, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authAppAdmin->password);
    }

    protected function updatePassword($authAppAdmin, string $newPassword): bool
    {

        $authAppAdmin->password = Hash::make($newPassword);
        return $authAppAdmin->save();
    }
}
