<?php

namespace App\Services\Auth\Teacher;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeTeacherPasswordService
{
    // Implement your logic here
    public function changeInstructorPassword($passwordData){

        $authTeacher = auth()->guard('teacher')->user();

        if (!$this->checkCurrentPassword($authTeacher, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }

        if ($this->updatePassword($authTeacher, $passwordData["new_password"])) {
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
