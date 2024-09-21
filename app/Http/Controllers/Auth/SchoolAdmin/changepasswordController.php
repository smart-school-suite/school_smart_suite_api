<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class changepasswordController extends Controller
{
    public function change_schooladmin_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authenticated_schooladmin = auth()->guard('schooladmin')->user();

        if (!$this->checkCurrentPassword($authenticated_schooladmin, $request->current_password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'status' => 'ok',
                    'message' => 'Current password is incorrect.'
                ],
            ]);
        }

        

        if ($this->updatePassword($authenticated_schooladmin, $request->new_password)) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Password changed successfully.'
            ], 200);
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
