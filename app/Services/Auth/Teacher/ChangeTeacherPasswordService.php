<?php

namespace App\Services\Auth\Teacher;

use App\Exceptions\AppException;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;

class ChangeTeacherPasswordService
{
    public function changeInstructorPassword($passwordData)
    {

        $authTeacher = auth()->guard('teacher')->user();

        if (!$authTeacher) {
            throw new AppException(
                "No authenticated teacher session found.",
                401,
                "Authentication Required 🔒",
                "You must be logged in as a teacher to change your password. Please log in and try again.",
                null
            );
        }

        if (!$this->checkCurrentPassword($authTeacher, $passwordData["current_password"])) {
            throw new AppException(
                "The provided current password is not valid for teacher ID '{$authTeacher->id}'.",
                401,
                "Incorrect Current Password ❌",
                "The password you entered for your current password is not correct. Please re-enter your current password accurately and try again.",
                null
            );
        }

        if ($this->updatePassword($authTeacher, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }

        throw new AppException(
            "Password update failed for teacher ID '{$authTeacher->id}' due to a database issue.",
            500,
            "Password Change Failed 🛑",
            "We were unable to save your new password due to a system error. Please try again in a moment or contact support.",
            null
        );
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
