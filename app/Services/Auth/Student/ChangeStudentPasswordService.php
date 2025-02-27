<?php

namespace App\Services\Auth\Student;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
class ChangeStudentPasswordService
{
    // Implement your logic here
    public function changeSchoolAdminPassword($passwordData){

        $authenticated_student = auth()->guard('student')->user();

        if (!$this->checkCurrentPassword($authenticated_student, $passwordData["current_password"])) {
            return ApiResponseService::error("Current Password is incorrect", null, 404);
        }

        if ($this->updatePassword($authenticated_student, $passwordData["new_password"])) {
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
