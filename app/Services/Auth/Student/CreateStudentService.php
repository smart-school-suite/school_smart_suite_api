<?php

namespace App\Services\Auth\Student;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
class CreateStudentService
{
    // Implement your logic here
    public function createStudent($studentData, $currentSchool){
        try {
            DB::beginTransaction();
            $find_specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->where('level_id', $studentData["level_id"])
                ->findOrFail($studentData["specialty_id"]);
            $student = Student::create([
                'name' => $studentData["name"],
                'first_name' => $studentData["first_name"],
                'last_name' => $studentData["last_name"],
                'DOB' => $studentData["DOB"],
                'guadian_id' => $studentData["guadian_id"],
                'gender' => $studentData["gender"],
                'phone_one' => $studentData["phone_one"],
                'level_id' => $studentData["level_id"],
                'specialty_id' => $studentData["specialty_id"],
                'department_id' => $studentData["department_id"],
                'email' => $studentData["email"],
                'student_batch_id' => $studentData["student_batch_id"],
                'school_branch_id' => $currentSchool->id,
                'payment_format' => $studentData["payment_format"],
                'password' => Hash::make($studentData["password"]),
            ]);
            RegistrationFee::create([
                'level_id' => $studentData["level_id"],
                'specialty_id' => $studentData["specialty_id"],
                'school_branch_id' => $currentSchool->id,
                'amount' => $find_specialty->registration_fee,
                'student_id' => $student->id,
            ]);
            TuitionFees::create([
                'level_id' => $studentData["level_id"],
                'specialty_id' => $studentData["specialty_id"],
                'school_branch_id' => $currentSchool->id,
                'tution_fee_total' => $find_specialty->school_fee,
                'student_id' => $student->id,
            ]);
            DB::commit();

            return $student;
        } catch (QueryException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
