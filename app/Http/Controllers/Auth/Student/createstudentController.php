<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CreateStudentController extends Controller
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
            'guadian_id' => 'required|string',
            'password' => 'required|string|min:8',
            'student_batch_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->where('level_id', $request->level_id)
                ->findOrFail($request->specialty_id);
            $student = Student::create([
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
                'payment_format' => $validatedData['payment_format'],
                'password' => Hash::make($validatedData['password']),
            ]);
            RegistrationFee::create([
                'level_id' => $validatedData['level_id'],
                'specialty_id' => $validatedData['specialty_id'],
                'school_branch_id' => $currentSchool->id,
                'amount' => $find_specialty->registration_fee,
                'student_id' => $student->id,
            ]);
            TuitionFees::create([
                'level_id' => $validatedData['level_id'],
                'specialty_id' => $validatedData['specialty_id'],
                'school_branch_id' => $currentSchool->id,
                'tution_fee_total' => $find_specialty->school_fee,
                'student_id' => $student->id,
            ]);
            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => 'Student created successfully'
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
