<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ChangeAppAdminPassword extends Controller
{
    //changeedumanagepasswordcontroller
    public function change_edumanageadmin_password(Request $request){
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $authenticated_edumanageadmin = auth()->guard('edumanageadmin')->user();

        if (!$this->checkCurrentPassword($authenticated_edumanageadmin, $request->current_password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'status' => 'ok',
                    'message' => 'Current password is incorrect.'
                ],
            ]);
        }



        if ($this->updatePassword($authenticated_edumanageadmin, $request->new_password)) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Password changed successfully.'
            ], 200);
        }
    }

    protected function checkCurrentPassword($authenticated_edumanageadmin, string $currentPassword): bool
    {
        return Hash::check($currentPassword, $authenticated_edumanageadmin->password);
    }

    protected function updatePassword($authenticated_edumanageadmin, string $newPassword): bool
    {

        $authenticated_edumanageadmin->password = Hash::make($newPassword);
        return $authenticated_edumanageadmin->save();
    }
}
