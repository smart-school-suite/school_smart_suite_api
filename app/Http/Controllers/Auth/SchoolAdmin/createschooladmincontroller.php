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
            'name' => "required|string",
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string',
            'employment_status' => 'required|string',
            'work_location' => 'required|string',
            'position' => 'required|string',
            'hire_date' => 'required|date',
            'salary' => 'required',
        ]);

        $new_school_admin_instance = new Schooladmin();
        $new_school_admin_instance->name = $request->name;
        $new_school_admin_instance->email = $request->email;
        $new_school_admin_instance->role = $request->role;
        $new_school_admin_instance->password = Hash::make($request->password);
        $new_school_admin_instance->employment_status = $request->employment_status;
        $new_school_admin_instance->work_location = $request->work_location;
        $new_school_admin_instance->position = $request->position;
        $new_school_admin_instance->hire_date = $request->hire_date;
        $new_school_admin_instance->salary = $request->salary;
        $new_school_admin_instance->school_branch_id = $currentSchool->id;

        $new_school_admin_instance->save();

        $new_school_admin_instance->assignRole('schoolAdmin');
        return response()->json([
            'status' => 'ok',
            'message' => 'School admin created succesfully',
            'school_admin_data' => $new_school_admin_instance,
        ], 200);
    }
}
