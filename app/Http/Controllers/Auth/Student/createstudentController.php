<?php

namespace App\Http\Controllers\Auth\Student;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CreateStudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class CreateStudentController extends Controller
{
    //
    public function create_student(CreateStudentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            DB::beginTransaction();
            $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->where('level_id', $request->level_id)
                ->findOrFail($request->specialty_id);
            $student = Student::create([
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'DOB' => $request->DOB,
                'guadian_one_id' => $request->guadian_one_id,
                'guadian_two_id' => $request->guadian_two_id,
                'gender' => $request->gender,
                'phone_one' => $request->phone_one,
                'level_id' => $request->level_id,
                'specialty_id' => $request->specialty_id,
                'department_id' => $request->department_id,
                'email' => $request->email,
                'student_batch_id' => $request->student_batch_id,
                'school_branch_id' => $currentSchool->id,
                'payment_format' => $request->payment_format,
                'password' => Hash::make($request->password),
            ]);
            RegistrationFee::create([
                'level_id' => $request->level_id,
                'specialty_id' => $request->specialty_id,
                'school_branch_id' => $currentSchool->id,
                'amount' => $find_specialty->registration_fee,
                'student_id' => $student->id,
            ]);
            TuitionFees::create([
                'level_id' => $request->level_id,
                'specialty_id' => $request->specialty_id,
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
