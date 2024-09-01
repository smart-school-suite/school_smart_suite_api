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
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        $new_school_admin_instance = new Edumanageadmin();
        $new_school_admin_instance->name = $request->name;
        $new_school_admin_instance->email = $request->email;
        $new_school_admin_instance->password = Hash::make($request->password);

        $new_school_admin_instance->save();

        return response()->json(['message' => 'School admin created succesfully'], 200);
    }
}
