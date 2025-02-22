<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetAuthSchoolAdminController extends Controller
{
    //
    //getauthenticatedschoolcontroller
    public function get_authenticated_school_admin(Request $request){
        $schooladmin_authenticated_data = auth()->guard('schooladmin')->user();
        return response()->json([
            'status' => 'ok',
            "message" => 'school admin user fetched succefully',
            'schooladmin_user' => $schooladmin_authenticated_data
        ], 200);
    }
}
