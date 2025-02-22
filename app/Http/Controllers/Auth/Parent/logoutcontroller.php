<?php

namespace App\Http\Controllers\Auth\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    //
    public function logout_parent(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}
