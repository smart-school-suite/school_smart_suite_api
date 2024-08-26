<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logoutcontroller extends Controller
{
    //
    public function logout_parent(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
