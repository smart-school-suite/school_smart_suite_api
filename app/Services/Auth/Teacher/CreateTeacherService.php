<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\StatisticalJobs\OperationalJobs\TeacherRegistrationStatsJob;
use App\Models\Teacher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeacherService
{
    // Implement your logic here
    public function createInstructor($teacherData, $currentSchool)
    {
        try {
            DB::beginTransaction();
            $password = $this->generateRandomPassword();
            $instructor = new Teacher();
            $instructorId = Str::uuid();
            $instructor->id = $instructorId;
            $instructor->name = $teacherData["name"];
            $instructor->email = $teacherData["email"];
            $instructor->first_name = $teacherData['first_name'];
            $instructor->last_name = $teacherData['last_name'];
            $instructor->gender = $teacherData['gender'];
            $instructor->password = Hash::make($password);
            $instructor->phone_one = $teacherData["phone_one"];
            $instructor->address = $teacherData['address'] ?? null;
            $instructor->school_branch_id = $currentSchool->id;
            $instructor->save();
            $instructor->assignRole('teacher');
            DB::commit();
            SendPasswordVaiMailJob::dispatch($password, $teacherData['email']);
            TeacherRegistrationStatsJob::dispatch($instructorId, $currentSchool->id);
            return $instructor;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
