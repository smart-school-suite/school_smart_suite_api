<?php

namespace App\Services\Auth\Guardian;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeGaurdianPasswordService
{
    // Implement your logic here

    public function changePasswordParent($passwordData){

        $authenticated_parent = auth()->guard('parent')->user();

        if (!$this->checkCurrentPassword($authenticated_parent, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }


        if ($this->updatePassword($authenticated_parent, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }
    }

    protected function checkCurrentPassword($authenticated_parent, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_parent->password);
    }

    protected function updatePassword($authenticated_parent, string $newPassword): bool
    {

        $authenticated_parent->password = Hash::make($newPassword);
        return $authenticated_parent->save();
    }
}
