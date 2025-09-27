<?php

namespace App\Services\Auth\SchoolAdmin;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AuthException;
class ChangeSchoolAdminPasswordService
{
    // Implement your logic here
      public function changeSchoolAdminPassword(array $passwordData)
    {
        $authSchoolAdmin = auth()->guard('schooladmin')->user();

        if (!$this->checkCurrentPassword($authSchoolAdmin, $passwordData["current_password"])) {
            throw new AuthException("Current password is incorrect.", 401, "Authentication Failed", "The current password you entered does not match our records.");
        }

        if ($this->checkCurrentPassword($authSchoolAdmin, $passwordData["new_password"])) {
             throw new AuthException("You cannot reuse your current password.", 409, "Password Conflict", "Your new password cannot be the same as your old password. Please choose a different one.");
        }

        if (strlen($passwordData['new_password']) < 8) {
            throw new AuthException("The new password must be at least 8 characters long.", 400, "Validation Error", "Please choose a new password with a minimum of 8 characters.");
        }

        if (!$this->updatePassword($authSchoolAdmin, $passwordData["new_password"])) {
            throw new AuthException("An unexpected error occurred while saving the new password.", 500, "Server Error", "We encountered an issue while updating your password. Please try again later.");
        }
    }

    protected function checkCurrentPassword($authSchoolAdmin, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authSchoolAdmin->password);
    }

    protected function updatePassword($authSchoolAdmin, string $newPassword): bool
    {
        $authSchoolAdmin->password = Hash::make($newPassword);
        return $authSchoolAdmin->save();
    }
}
