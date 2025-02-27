<?php

namespace App\Services\Auth\AppAdmin;

use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeAppAdminPasswordService
{
    // Implement your logic here

    public function changeAppAdminPassword($passwordData){

        $authenticated_edumanageadmin = auth()->guard('edumanageadmin')->user();

        if (!$this->checkCurrentPassword($authenticated_edumanageadmin, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }



        if ($this->updatePassword($authenticated_edumanageadmin, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }
    }

    protected function checkCurrentPassword($authenticated_edumanageadmin, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_edumanageadmin->password);
    }

    protected function updatePassword($authenticated_edumanageadmin, string $newPassword): bool
    {

        $authenticated_edumanageadmin->password = Hash::make($newPassword);
        return $authenticated_edumanageadmin->save();
    }
}
