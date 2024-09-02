<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class changepasswordController extends Controller
{
    public function change_student_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authenticated_student = auth()->guard('student')->user();

        if (!$this->checkCurrentPassword($authenticated_student, $request->current_password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        

        if ($this->updatePassword($authenticated_student, $request->new_password)) {
            return response()->json(['message' => 'Password changed successfully.'], 200);
        }
    }

    protected function checkCurrentPassword($authenticated_student, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_student->password);
    }

    protected function updatePassword($authenticated_student, string $newPassword): bool
    {

        $$authenticated_student->password = Hash::make($newPassword);
        return $authenticated_student->save(); 
    }
}
