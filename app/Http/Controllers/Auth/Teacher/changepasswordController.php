<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ChangePasswordController extends Controller
{
    public function change_teacher_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authTeacher = auth()->guard('teacher')->user();

        if (!$this->checkCurrentPassword($authTeacher, $request->current_password)) {

            return ApiResponseService::error("Current Password is incorrect it miht have been changed some months ago", null, 409);
        }
        if ($this->updatePassword($authTeacher, $request->new_password)) {
            return ApiResponseService::success("Password Changed Succesfully", null, null, 200);
        }
    }
    protected function checkCurrentPassword($authTeacher, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authTeacher->password);
    }

    protected function updatePassword($authTeacher, string $newPassword): bool
    {

        $authTeacher->password = Hash::make($newPassword);
        return $authTeacher->save();
    }
}
