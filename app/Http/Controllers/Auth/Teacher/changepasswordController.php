<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class changepasswordController extends Controller
{
    public function change_teacher_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authenticated_teacher = auth()->guard('teacher')->user();

        if (!$this->checkCurrentPassword($authenticated_teacher, $request->current_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        

        if ($this->updatePassword($authenticated_teacher, $request->new_password)) {
            return response()->json(['message' => 'Password changed successfully.'], 200);
        }
    }

    protected function checkCurrentPassword($authenticated_teacher, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_teacher->password);
    }

    protected function updatePassword($authenticated_teacher, string $newPassword): bool
    {

        $authenticated_teacher->password = Hash::make($newPassword);
        return $authenticated_teacher->save(); 
    }
}
