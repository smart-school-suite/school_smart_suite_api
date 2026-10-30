<?php

namespace App\Services\Auth\Teacher;

use App\Models\Teacher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AuthException;
use App\Exceptions\AppException;

class CreateTeacherService
{
    public function createInstructor(array $teacherData, object $currentSchool, object $authAdmin)
    {
        try {
            if (Teacher::where('email', $teacherData['email'])->where('school_branch_id', $currentSchool->id)->exists()) {
                throw new AuthException(
                    "This email address is already in use at this school branch.",
                    409,
                    "Email Already Registered",
                    "The email address '{$teacherData['email']}' is already linked to another staff member at this school branch. Please use a different email address or search for the existing staff account to update it."
                );
            }

            if (Teacher::where('name', $teacherData['name'])->where('school_branch_id', $currentSchool->id)->exists()) {
                throw new AppException(
                    "A teacher with the name '{$teacherData['name']}' already exists at this school branch.",
                    409,
                    "Teacher Name Already Exists",
                    "An instructor named '{$teacherData['name']}' is already registered in this school system. If this is a different person with the same name, please include a middle name, initial, or suffix to distinguish them.",
                    null
                );
            }

            $levelIds = $teacherData['level_ids'] ?? [];
            $qualifications = $teacherData['qualifications'] ?? [];

            DB::beginTransaction();

            $password = $this->generateRandomPassword();

            $instructor = Teacher::create([
                'name'             => $teacherData['name'],
                'username'         => $this->generateUsername($teacherData['name']),
                'email'            => $teacherData['email'],
                'first_name'       => $teacherData['first_name'],
                'last_name'        => $teacherData['last_name'],
                'gender_id'        => $teacherData['gender_id'],
                'password'         => Hash::make($password),
                'phone'            => $teacherData['phone'],
                'address'          => $teacherData['address'] ?? null,
                'status'           => $teacherData['status'] ?? 'active',
                'school_branch_id' => $currentSchool->id,
            ]);

            $instructor->assignRole('teacher');

            $levelsSyncData = [];
            foreach ($levelIds as $levelId) {
                if (!empty($levelId)) {
                    $levelsSyncData[$levelId] = [
                        'school_branch_id' => $currentSchool->id
                    ];
                }
            }
            $instructor->levels()->sync($levelsSyncData);

            $qualificationsSyncData = [];
            foreach ($qualifications as $item) {
                if (!empty($item['qualification_id'])) {
                    $qualificationsSyncData[$item['qualification_id']] = [
                        'school_branch_id' => $currentSchool->id,
                        'field_of_study'   => $item['field_of_study'] ?? null,
                        'year'             => $item['year'] ?? null,
                        'institution'      => $item['institution'] ?? null
                    ];
                }
            }
            $instructor->qualifications()->sync($qualificationsSyncData);

            DB::commit();

            return $instructor;
        } catch (AuthException | AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "Failed to create the instructor record: " . $e->getMessage(),
                500,
                "Unable to Complete Registration",
                "We encountered an unexpected technical issue while trying to create this instructor profile, and all changes have been safely reverted. Please double-check the entered information and try submitting again. If the issue continues, reach out to technical support for assistance.",
                null
            );
        }
    }
    private function generateUsername(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);

        $parts = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        $base = match (true) {
            empty($parts)       => 'user',
            count($parts) === 1 => $parts[0],
            default             => $parts[0][0] . end($parts),
        };

        $base = strlen($base) < 3 ? str_pad($base, 3, '0') : $base;

        $base = substr($base, 0, 20);

        $username = $base;
        $counter = 1;
        while (Teacher::where('username', $username)->exists()) {
            $username = $base . $counter++;
        }

        return $username;
    }
    private function generateRandomPassword($length = 10): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
