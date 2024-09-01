<?php

namespace App\Http\Controllers\Auth\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class createschooladmincontroller extends Controller
{
    //
    public function create_school_admin(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);
        
        $new_school_admin_instance = new Schooladmin();
        $new_school_admin_instance->name = $request->name;
        $new_school_admin_instance->email = $request->email;
        $new_school_admin_instance->school_branch = $currentSchool->id;
        $new_school_admin_instance->password = Hash::make($request->password);

        $new_school_admin_instance->save();

        return response()->json(['message' => 'School admin created succesfully'], 200);
    }
}
