<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getauthenticatededumanageadmincontroller extends Controller
{
    public function get_authenticated_eduamanageadmin(Request $request){
        $eduamanageadmin_authenticated_data = auth()->guard('edumanageadmin')->user();
         return response()->json([
            'status' => 'ok',
            'message' => 'authenticated user fetched succefully',
            'eduamanageadmin_user' => $eduamanageadmin_authenticated_data
         ], 200);
    }
}
