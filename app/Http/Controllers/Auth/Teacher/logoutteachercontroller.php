<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logoutteachercontroller extends Controller
{
    //
    public function logout_teacher(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
