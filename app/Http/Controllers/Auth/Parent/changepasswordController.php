<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class changepasswordController extends Controller
{
    //
    public function change_parent_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authenticated_parent = auth()->guard('parent')->user();

        if (!$this->checkCurrentPassword($authenticated_parent, $request->current_password)) {
            throw ValidationException::withMessages([
                'status' => 'ok',
                'current_password' => 'Current password is incorrect.',
            ]);
        }


        if ($this->updatePassword($authenticated_parent, $request->new_password)) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Password changed successfully.'
            ], 200);
        }
    }

    protected function checkCurrentPassword($authenticated_parent, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_parent->password);
    }

    protected function updatePassword($authenticated_parent, string $newPassword): bool
    {

        $authenticated_parent->password = Hash::make($newPassword);
        return $authenticated_parent->save(); 
    }
}
