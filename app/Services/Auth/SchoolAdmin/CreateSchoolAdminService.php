<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Exceptions\AuthException;
use App\Services\Helpers\Actor\ActorHelperService;

class CreateSchoolAdminService
{

    public function createSchoolAdmin(array $schoolAdminData, object $currentSchool, $authAdmin)
    {
        try {
            $actorHelper = app(ActorHelperService::class);
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = $data['name'] ?? 'Admin';
            }

            $username = $actorHelper->generateUsername($fullName, Schooladmin::class);
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
            $schoolAdmin->username = $username;
            $schoolAdmin->password = Hash::make($password);
            $schoolAdmin->first_name = $schoolAdminData["first_name"];
            $schoolAdmin->last_name = $schoolAdminData["last_name"];
            $schoolAdmin->school_branch_id = $currentSchool->id;
            $schoolAdmin->save();
            $schoolAdmin->assignRole('schoolAdmin');

            SendPasswordVaiMailJob::dispatch($password, $schoolAdminData['email']);
            return $schoolAdmin;
        } catch (AuthException $e) {
            throw $e;
        } catch (Exception $e) {
            // Log the full error with context
            Log::error('Failed to create school admin', [
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'school_admin_data' => $schoolAdminData,
                'current_school_id' => $currentSchool->id ?? null,
                'actor_admin_id'    => $authAdmin->id ?? null,
            ]);

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
