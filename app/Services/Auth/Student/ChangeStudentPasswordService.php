<?php

namespace App\Services\Auth\Student;

use App\Exceptions\AuthException;
use Illuminate\Support\Facades\Hash;

class ChangeStudentPasswordService
{
    public function changeStudentPassword(array $passwordData)
    {
        $authStudent = auth()->guard('student')->user();

        if (!$authStudent) {
            throw new AuthException("Authentication failed. Please log in again.", 401, "Authentication Failed", "You must be logged in as a student to change your password.");
        }

        if (!$this->checkCurrentPassword($authStudent, $passwordData["current_password"])) {
            throw new AuthException("Current password is incorrect.", 401, "Authentication Failed", "The current password you entered does not match our records.");
        }

        if ($this->checkCurrentPassword($authStudent, $passwordData["new_password"])) {
            throw new AuthException("You cannot reuse your current password.", 409, "Password Conflict", "Your new password cannot be the same as your old password. Please choose a different one.");
        }

        if (strlen($passwordData['new_password']) < 8) {
            throw new AuthException("The new password must be at least 8 characters long.", 400, "Validation Error", "Please choose a new password with a minimum of 8 characters.");
        }

        if (!$this->updatePassword($authStudent, $passwordData["new_password"])) {
            throw new AuthException("An unexpected error occurred while saving the new password.", 500, "Server Error", "We encountered an issue while updating your password. Please try again later.");
        }
    }

    protected function checkCurrentPassword($authStudent, string $password): bool
    {
        return Hash::check($password, $authStudent->password);
    }

    protected function updatePassword($authStudent, string $newPassword): bool
    {
        $authStudent->password = Hash::make($newPassword);
        return $authStudent->save();
    }
}
