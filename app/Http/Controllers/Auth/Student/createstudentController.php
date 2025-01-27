<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class createstudentController extends Controller
{
    //
    public function create_student(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $validatedData = $request->validate([
            'name' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'DOB' => 'required|string',
            'gender' => 'required|string',
            'phone_one' => 'required|string',
            'level_id' => 'required|string',
            'specialty_id' => 'required|string',
            'department_id' => 'required|string',
            'email' => 'required|email',
            'guadian_one_id' => 'required|string',
            'guadian_two_id' => 'sometimes|string',
            'password' => 'required|string|min:8',
            'student_batch_id' => 'required|string'
        ]);
        $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
            ->where('level_id', $request->level_id)
            ->find($request->specialty_id);

        Student::create([
            'name' => $validatedData['name'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'DOB' => $validatedData['DOB'],
            'guadian_one_id' => $validatedData['guadian_one_id'],
            'guadian_two_id' => $validatedData['guadian_two_id'],
            'gender' => $validatedData['gender'],
            'phone_one' => $validatedData['phone_one'],
            'level_id' => $validatedData['level_id'],
            'specialty_id' => $validatedData['specialty_id'],
            'department_id' => $validatedData['department_id'],
            'email' => $validatedData['email'],
            'student_batch_id' => $validatedData['student_batch_id'],
            'school_branch_id' => $currentSchool->id,
            'total_fee_debt' => floatval($find_specialty->school_fee) + floatval($find_specialty->registration_fee),
            'password' => Hash::make($validatedData['password']),
        ]);


        return response()->json([
            'status' => 'ok',
            'message' => 'student created succefully'
        ], 200);
    }
}
