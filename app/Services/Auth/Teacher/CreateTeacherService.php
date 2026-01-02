<?php

namespace App\Services\Auth\Teacher;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\StatisticalJobs\OperationalJobs\TeacherRegistrationStatsJob;
use App\Models\Teacher;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Exceptions\AuthException;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;
use App\Constant\Analytics\Operational\OperationalAnalyticsEvent as OperationalEvents;
use App\Events\Analytics\OperationalAnalyticsEvent;
class CreateTeacherService
{
    public function createInstructor($teacherData, $currentSchool, $authAdmin)
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
            $instructor->phone = $teacherData["phone"];
            $instructor->address = $teacherData['address'] ?? null;
            $instructor->school_branch_id = $currentSchool->id;
            $instructor->save();
            $instructor->assignRole('teacher');

            DB::commit();

            SendPasswordVaiMailJob::dispatch($password, $teacherData['email']);
            TeacherRegistrationStatsJob::dispatch($instructorId, $currentSchool->id);
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.teacher.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "teacherManagement",
                    "action" => "teacher.created",
                    "authAdmin" => $authAdmin,
                    "data" => $instructor,
                    "message" => "Teacher Created",
                ]
            );
            event(new OperationalAnalyticsEvent(
                 eventType:OperationalEvents::TEACHER_CREATED,
                 version:1,
                 payload:[
                    "school_branch_id" => $currentSchool->id,
                    "value" => 1,
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
    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
