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
           'email' => 'required|email|string',
           'password' => 'required|string|min:8',
           'phone_number' => 'required|string',
        ]);

         $new_teacher_instance = new Teacher();
         $new_teacher_instance->school_branch_id = $currentSchool->id;
         $new_teacher_instance->email = $request->email;
         $new_teacher_instance->phone_number = $request->phone_number;
         $new_teacher_instance->password = Hash::make($request->password);

         $new_teacher_instance->save();

         return response()->json(['message' => 'teacher created sucessfully'], 200);
    }
}
