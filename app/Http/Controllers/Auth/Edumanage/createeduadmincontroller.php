<?php

namespace App\Http\Controllers\Auth\Edumanage;

use App\Http\Controllers\Controller;
use App\Models\Edumanageadmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class createeduadmincontroller extends Controller
{
    //
    public function create_edumanage_admin(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string'
        ]);
        
        $new_school_admin_instance = new Edumanageadmin();
        $new_school_admin_instance->name = $request->name;
        $new_school_admin_instance->email = $request->email;
        $new_school_admin_instance->phone_number = $request->phone_number;
        $new_school_admin_instance->password = Hash::make($request->password);
         $new_school_admin_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Edumanage administrator created succesfully',
            'created_admin' => $new_school_admin_instance
        ], 200);
    }
}
