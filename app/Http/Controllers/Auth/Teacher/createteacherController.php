<?php

namespace App\Http\Controllers\Auth\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class createteacherController extends Controller
{
    //
    public function create_teacher(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
           'name' => 'required|String',
           'email' => 'required|email|string',
           'password' => 'required|string|min:8',
           'phone_one' => 'required|string',
           'employment_status' => 'required|string',
           'highest_qualification' => 'required|string',
           'field_of_study' => 'required|string',
           'years_experience' => 'required|integer',
           'salary' => 'required'
        ]);

         $new_teacher_instance = new Teacher();
         $new_teacher_instance->name = $request->name;
         $new_teacher_instance->email = $request->email;
         $new_teacher_instance->password = Hash::make($request->password);
         $new_teacher_instance->phone_one = $request->phone_one;
         $new_teacher_instance->employment_status = $request->employment_status;
         $new_teacher_instance->highest_qualification = $request->highest_qualification;
         $new_teacher_instance->field_of_study = $request->field_of_study;
         $new_teacher_instance->years_experience = $request->years_experience;
         $new_teacher_instance->salary = $request->salary;
         $new_teacher_instance->school_branch_id = $currentSchool->id;

         $new_teacher_instance->save();

         return response()->json([
            'status' => 'ok',
            'message' => 'teacher created sucessfully',
            'teacher' => $new_teacher_instance,
         ], 200);
    }
}
