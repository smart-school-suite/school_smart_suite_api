<?php

namespace App\Services\Auth\Student;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\NotificationJobs\SendAdminStudentCreatedNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\TuitionFeeStatJob;
use App\Jobs\StatisticalJobs\OperationalJobs\StudentRegistrationStatsJob;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\RegistrationFee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AuthException;
use App\Exceptions\AppException;

class CreateStudentService
{
    public function createStudent($studentData, $currentSchool)
    {
        try {

            $specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->with(['level'])
                ->findOrFail($studentData["specialty_id"]);


            if (Student::where('email', $studentData['email'])
                ->where('school_branch_id', $currentSchool->id)
                ->exists()
            ) {

                throw new AuthException(
                    "This student email address is already registered at this school branch.",
                    409,
                    "Student Email Already Exists 📧",
                    "The email '{$studentData['email']}' is already associated with a student account in your school branch. Please use a different email or check the existing account."
                );
            }

            if (Student::where('name', $studentData['name'])
                ->where('school_branch_id', $currentSchool->id)
                ->exists()
            ) {

                throw new AppException(
                    "A student with the name '{$studentData['name']}' already exists at this school branch.",
                    409,
                    "Duplicate Student Name 📛",
                    "A student with the exact name '{$studentData['name']}' is already registered at this school. Please ensure you are not creating a duplicate or consider adding a middle initial or suffix for differentiation.",
                    null
                );
            }

            DB::beginTransaction();

            $password = $this->generateRandomPassword();
            $randomId = Str::uuid()->toString();

            $student = new Student();
            $student->id = $randomId;
            $student->name = $studentData["name"];
            $student->first_name = $studentData["first_name"];
            $student->gender = $studentData['gender'];
            $student->phone_one = $studentData['phone_one'] ?? null;
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
            $student->assignRole('student');

            $registrationFeeId = Str::uuid();
            RegistrationFee::create([
                'id' => $registrationFeeId,
                'level_id' => $specialty->level_id,
                'specialty_id' => $specialty->id,
                'school_branch_id' => $currentSchool->id,
                'amount' => $specialty->registration_fee,
                'status' => "not paid",
                'student_id' => $randomId,
            ]);

            $tuitionFeeId = Str::uuid();
            TuitionFees::create([
                'id' => $tuitionFeeId,
                'level_id' => $specialty->level_id,
                'specialty_id' => $specialty->id,
                'amount_paid' => 0.00,
                'amount_left' => $specialty->school_fee,
                'school_branch_id' => $currentSchool->id,
                'tution_fee_total' => $specialty->school_fee,
                'student_id' => $randomId,
            ]);

            DB::commit();

            SendPasswordVaiMailJob::dispatch($password, $studentData["email"]);
            TuitionFeeStatJob::dispatch($tuitionFeeId, $currentSchool->id);
            StudentRegistrationStatsJob::dispatch($randomId, $currentSchool->id);
            SendAdminStudentCreatedNotificationJob::dispatch(
                $specialty->specialty_name,
                $studentData["name"],
                $specialty->level->name,
                $currentSchool->id
            );

            return $student;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "The specified specialty ID '{$studentData["specialty_id"]}' was not found.",
                404,
                "Specialty Not Found 🧩",
                "The requested subject or specialty (ID: {$studentData["specialty_id"]}) does not exist or is not available at your school branch.",
                null
            );
        } catch (AuthException | AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new AppException(
                "A fatal system error occurred during student registration: " . $e->getMessage(),
                500,
                "Registration Failed Due to System Error 🛑",
                "We were unable to complete the student registration due to an unexpected system issue. The attempt has been rolled back. Please try again or contact support.",
                null
            );
        }
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
