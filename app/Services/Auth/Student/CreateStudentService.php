<?php

namespace App\Services\Auth\Student;

use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CreateStudentService
{
    public function createStudent($studentData, $currentSchool)
    {
        try {
            $specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->where('level_id', $studentData["level_id"])
                ->findOrFail($studentData["specialty_id"]);

            $password = $this->generateRandomPassword();
            $randomId = Str::uuid()->toString();
            $student = new Student();
            $student->id = $randomId;
            $student->name = $studentData["name"];
            $student->first_name = $studentData["first_name"];
            $student->last_name = $studentData["last_name"];
            $student->guadian_id = $studentData["guadian_id"];
            $student->level_id = $studentData["level_id"];
            $student->specialty_id = $studentData["specialty_id"];
            $student->department_id = $studentData["department_id"];
            $student->email = $studentData["email"];
            $student->student_batch_id = $studentData["student_batch_id"];
            $student->school_branch_id = $currentSchool->id;
            $student->payment_format = $studentData["payment_format"];
            $student->password = Hash::make($password);
            $student->save();

            RegistrationFee::create([
                'level_id' => $studentData["level_id"],
                'specialty_id' => $studentData["specialty_id"],
                'school_branch_id' => $currentSchool->id,
                'amount' => $specialty->registration_fee,
                'student_id' => $randomId,
            ]);

            TuitionFees::create([
                'level_id' => $studentData["level_id"],
                'specialty_id' => $studentData["specialty_id"],
                'school_branch_id' => $currentSchool->id,
                'tution_fee_total' => $specialty->school_fee,
                'student_id' => $randomId,
            ]);

            return [
                'student_id' => $student->id,
                'generated_password' => $password
            ];
        } catch (QueryException $e) {

            Log::error($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
