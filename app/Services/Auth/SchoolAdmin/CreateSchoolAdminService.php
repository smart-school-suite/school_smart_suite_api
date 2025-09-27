<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\StatisticalJobs\OperationalJobs\SchoolAdminStatJob;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use App\Exceptions\AuthException;
class CreateSchoolAdminService
{

    public function createSchoolAdmin($schoolAdminData, $currentSchool)
    {
        try {
            $existingAdmin = Schooladmin::where('email', $schoolAdminData['email'])
                                     ->where('school_branch_id', $currentSchool->id)
                                     ->first();

            if ($existingAdmin) {
                throw new AuthException(
                    "This email address is already in use at this school branch.",
                    409,
                    "Email Already Exists",
                    "The email '{$schoolAdminData['email']}' is already associated with an account in your school branch. Please use a different email or check the existing account."
                );
            }

            $password = $this->generateRandomPassword();
            $schoolAdmin = new Schooladmin();
            $schoolAdminId = Str::uuid()->toString();

            $schoolAdmin->id = $schoolAdminId;
            $schoolAdmin->name = $schoolAdminData["name"];
            $schoolAdmin->email = $schoolAdminData["email"];
            $schoolAdmin->password = Hash::make($password);
            $schoolAdmin->first_name = $schoolAdminData["first_name"];
            $schoolAdmin->last_name = $schoolAdminData["last_name"];
            $schoolAdmin->school_branch_id = $currentSchool->id;
            $schoolAdmin->save();
            $schoolAdmin->assignRole('schoolAdmin');

            SendPasswordVaiMailJob::dispatch($password, $schoolAdminData['email']);
            SchoolAdminStatJob::dispatch($currentSchool->id, $schoolAdminId);

            return $schoolAdmin;

        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AuthException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "We were unable to create the school administrator account due to an unexpected issue. We're looking into it."
            );
        }
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
