<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logoutschooladmincontroller extends Controller
{
    //
    public function logout_school_admin(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
