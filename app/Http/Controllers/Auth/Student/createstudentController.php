<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class createstudentController extends Controller
{
    //
    public function create_student(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'DOB' => 'required|string',
            'gender' => 'required|string',
            'phone_number' => 'required|string',
            'level_id' => 'required|string',
            'school_branch_id' => 'required|string',
            'specialty_id' => 'required|string',
            'department_id' => 'required|string',
            'religion' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        Student::create([
            'name' => $validatedData['name'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'DOB' => $validatedData['DOB'],
            'gender' => $validatedData['gender'],
            'phone_number' => $validatedData['phone_number'],
            'level_id' => $validatedData['level_id'],
            'specialty_id' => $validatedData['specialty_id'],
            'department_id' => $validatedData['department_id'],
            'religion' => $validatedData['religion'],
            'email' => $validatedData['email'],
            'school_branch_id' => $validatedData['school_branch_id'],
            'password' => Hash::make($validatedData['password']), // Hash the password
        ]);
 

        return response()->json(['message' => 'student created succefully'], 200);

    }
}
