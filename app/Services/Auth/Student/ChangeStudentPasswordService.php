<?php

namespace App\Services\Auth\Student;

use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AppException;

class ChangeStudentPasswordService
{
    // Implement your logic here
    public function changeStudentPassword($passwordData)
    {

        $authenticated_student = auth()->guard('student')->user();

        if (!$authenticated_student) {
            throw new AppException(
                "No authenticated student session found.",
                401,
                "Authentication Required 🔒",
                "You must be logged in as a student to change your password. Please log in and try again.",
                null
            );
        }

        if (!$this->checkCurrentPassword($authenticated_student, $passwordData["current_password"])) {
            throw new AppException(
                "The provided current password is not valid for student ID '{$authenticated_student->id}'.",
                401,
                "Incorrect Current Password ❌",
                "The password you entered for your current password is not correct. Please re-enter your current password accurately and try again.",
                null
            );
        }

        if ($this->updatePassword($authenticated_student, $passwordData["new_password"])) {
            return ApiResponseService::success("Password changed successfully", null, null, 200);
        }

        throw new AppException(
            "Password update failed for student ID '{$authenticated_student->id}' due to a database issue.",
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
