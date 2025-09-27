<?php

namespace App\Services\Auth\SchoolAdmin;

use App\Models\Schooladmin;
use App\Exceptions\AuthException;
class UpdateSchoolAdminProfileService
{
    public function updateSchoolAdminProfile(array $updateData, $currentSchool)
    {
        $authSchoolAdmin = auth()->guard('schooladmin')->user();

        if (!$authSchoolAdmin) {
            throw new AuthException(
                "Authentication failed. Please log in again.",
                401,
                "Authentication Failed",
                "You must be logged in to update your profile."
            );
        }

        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->find($authSchoolAdmin->id);

        if (!$schoolAdmin) {
            throw new AuthException(
                "User not found in the current school branch.",
                404,
                "User Not Found",
                "The user account could not be located. This may be a configuration issue. Please contact support."
            );
        }

        try {
            $filteredData = array_filter($updateData, function($value) {
                return !is_null($value) && $value !== '';
            });

            if (isset($filteredData['email']) && $filteredData['email'] !== $schoolAdmin->email) {
                $existingAdmin = Schooladmin::where('email', $filteredData['email'])
                    ->where('school_branch_id', $currentSchool->id)
                    ->first();

                if ($existingAdmin) {
                    throw new AuthException(
                        "This email address is already in use at this school branch.",
                        409,
                        "Email Already Exists",
                        "The email '{$filteredData['email']}' is already associated with an account in your school branch."
                    );
                }
            }

            $isUpdated = $schoolAdmin->update($filteredData);

            if (!$isUpdated) {
                throw new AuthException(
                    "Failed to update the user profile due to a database error.",
                    500,
                    "Database Error",
                    "We were unable to save your profile changes. Please try again later."
                );
            }

            return true;
        } catch (AuthException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AuthException(
                "An unexpected error occurred. Please try again later.",
                500,
                "Server Error",
                "An unexpected system error occurred while updating your profile."
            );
        }
    }
}
