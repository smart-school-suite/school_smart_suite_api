<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\StatisticalJobs\OperationalJobs\TeacherRegistrationStatsJob;
use App\Models\Teacher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\AuthException;
use App\Exceptions\AppException;
// use App\Events\Actions\AdminActionEvent;
use App\Constant\Analytics\Operational\OperationalAnalyticsEvent as OperationalEvents;
use App\Events\Analytics\OperationalAnalyticsEvent;

class CreateTeacherService
{
    public function createInstructor(array $teacherData, object $currentSchool, object $authAdmin)
    {
        try {
            if (Teacher::where('email', $teacherData['email'])->where('school_branch_id', $currentSchool->id)->exists()) {
                throw new AuthException(
                    "This email address is already in use at this school branch.",
                    409,
                    "Email Already Exists",
                    "The email '{$teacherData['email']}' is already associated with an account in your school branch. Please use a different email or check the existing account."
                );
            }

            if (Teacher::where('name', $teacherData['name'])->where('school_branch_id', $currentSchool->id)->exists()) {
                throw new AppException(
                    "A teacher with the name '{$teacherData['name']}' already exists at this school branch.",
                    409,
                    "Duplicate Teacher Name 📛",
                    "A teacher with the exact name '{$teacherData['name']}' is already registered at this school. Please ensure you are not creating a duplicate or consider adding a middle initial or suffix to differentiate the record.",
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
                'profile_picture'  => $teacherData['profile_picture'] ?? null,
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
                        'field_of_study'   => $item['field_of_study'] ?? null
                    ];
                }
            }
            $instructor->qualifications()->sync($qualificationsSyncData);

            DB::commit();

            SendPasswordVaiMailJob::dispatch($password, $teacherData['email']);
            TeacherRegistrationStatsJob::dispatch($instructor->id, $currentSchool->id);

            // AdminActionEvent::dispatch([
            //     "permissions"  => ["schoolAdmin.teacher.create"],
            //     "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
            //     "schoolBranch" => $currentSchool->id,
            //     "feature"      => "teacherManagement",
            //     "action"       => "teacher.created",
            //     "authAdmin"    => $authAdmin,
            //     "data"         => $instructor,
            //     "message"      => "Teacher Created",
            // ]);

            event(new OperationalAnalyticsEvent(
                eventType: OperationalEvents::TEACHER_CREATED,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "value"            => 1,
                ]
            ));

            return $instructor;
        } catch (AuthException | AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "Failed to create the instructor record: " . $e->getMessage(),
                500,
                "Instructor Creation Failed 🛑",
                "We were unable to create the new instructor account due to a system error. The operation has been rolled back. Please ensure all required fields are correct and try again, or contact support.",
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
