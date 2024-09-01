<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logouteduadmincontroller extends Controller
{
    //
    public function logout_eduadmin(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
