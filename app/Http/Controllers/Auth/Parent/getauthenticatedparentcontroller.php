<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getauthenticatedparentcontroller extends Controller
{
    //
    public function get_authenticated_parent(Request $request){
        $parent_authenticated_data = auth()->guard('parent')->user();
        return response()->json(['parent_user' => $parent_authenticated_data], 200);
    }
}
