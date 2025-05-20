<?php

namespace App\Services\Auth\Student;

use App\Jobs\SendPasswordMailJob;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CreateStudentService
{
    public function createStudent($studentData, $currentSchool)
    {
        try {
            $specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->findOrFail($studentData["specialty_id"]);

            $password = $this->generateRandomPassword();
            $randomId = Str::uuid()->toString();
            $student = new Student();
            $student->id = $randomId;
            $student->name = $studentData["name"];
            $student->first_name = $studentData["first_name"];
            $student->phone_one = $studentData['phone_one'];
            $student->last_name = $studentData["last_name"];
            $student->guardian_id = $studentData["guardian_id"];
            $student->email = $studentData["email"];
            $student->level_id = $specialty->level_id;
            $student->specialty_id = $specialty->id;
            $student->department_id = $specialty->department_id;
            $student->student_batch_id = $studentData["student_batch_id"];
            $student->school_branch_id = $currentSchool->id;
            $student->password = Hash::make($password);
            $student->save();

            RegistrationFee::create([
                'level_id' => $specialty->level_id,
                'specialty_id' => $specialty->id,
                'school_branch_id' => $currentSchool->id,
                'amount' => $specialty->registration_fee,
                'student_id' => $randomId,
            ]);

            TuitionFees::create([
                'level_id' => $specialty->level_id,
                'specialty_id' => $specialty->id,
                'amount_paid' => 0.00,
                'amount_left' => 0.00,
                'school_branch_id' => $currentSchool->id,
                'tution_fee_total' => $specialty->school_fee,
                'student_id' => $randomId,
            ]);
             SendPasswordMailJob::dispatch( $password, $studentData["email"]);
             $student->assignRole('student');
            return $student;
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
