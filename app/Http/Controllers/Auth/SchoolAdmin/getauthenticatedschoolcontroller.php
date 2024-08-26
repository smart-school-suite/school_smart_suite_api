<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getauthenticatedschoolcontroller extends Controller
{
    //
    public function get_authenticated_school_admin(Request $request){
        $schooladmin_authenticated_data = auth()->guard('schooladmin')->user();
        return response()->json(['schooladmin_user' => $schooladmin_authenticated_data], 200);
    }
}
