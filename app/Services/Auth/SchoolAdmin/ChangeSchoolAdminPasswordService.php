<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeSchoolAdminPasswordService
{
    // Implement your logic here
    public function changeSchoolAdminPassword($passwordData){

        $authenticated_schooladmin = auth()->guard('schooladmin')->user();

        if (!$this->checkCurrentPassword($authenticated_schooladmin, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }

        if ($this->updatePassword($authenticated_schooladmin, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }
    }

    protected function checkCurrentPassword($authenticated_schooladmin, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_schooladmin->password);
    }

    protected function updatePassword($authenticated_schooladmin, string $newPassword): bool
    {
        $authenticated_schooladmin->password = Hash::make($newPassword);
        return $authenticated_schooladmin->save();
    }
}
