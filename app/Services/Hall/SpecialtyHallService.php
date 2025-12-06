<?php

namespace App\Services\Hall;

use App\Models\Hall;
use App\Models\SpecialtyHall;
use App\Models\Specialty;
use App\Exceptions\AppException;
use App\Models\Student;
use App\Events\Actions\AdminActionEvent;
use Illuminate\Support\Facades\DB;

class SpecialtyHallService
{
    public function assignHallToSpecialty($currentSchool, $data, $authAdmin)
    {
        return DB::transaction(function () use ($currentSchool, $data, $authAdmin) {
            $hall = Hall::where("school_branch_id", $currentSchool->id)
                ->lockForUpdate()
                ->find($data['hall_id']);

            if (!$hall) {
                throw new AppException(
                    "Hall Not Found, it might have been deleted please try again",
                    404,
                    "Hall Not Found",
                    "The Hall you're trying to update was not found. It might have been deleted. Please verify and try again."
                );
            }

            $specialty = Specialty::where("school_branch_id", $currentSchool->id)
                ->with(['level'])
                ->find($data["specialty_id"]);

            if (!$specialty) {
                throw new AppException(
                    "The specialty you are trying to assign was not found.",
                    404,
                    "Specialty Not Found",
                    "We could not find the specialty with the provided ID for this school. It may have been deleted."
                );
            }

            $alreadyAssigned = SpecialtyHall::where("school_branch_id", $currentSchool->id)
                ->where("hall_id", $hall->id)
                ->where("specialty_id", $specialty->id)
                ->where("level_id", $specialty->level_id)
                ->exists();

            if ($alreadyAssigned) {
                throw new AppException(
                    "Specialty already assigned to this hall",
                    409,
                    "Duplicate Assignment",
                    "{$specialty->specialty_name} ({$specialty->level->level_name}) is already assigned to {$hall->name}."
                );
            }

            $studentCount = Student::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $specialty->id)
                ->where("level_id", $specialty->level_id)
                ->count();

            if ($studentCount > $hall->capacity) {
                throw new AppException(
                    "Student count exceeds hall capacity",
                    400,
                    "Capacity Exceeded",
                    "There are {$studentCount} students in {$specialty->specialty_name} ({$specialty->level->level_name}), which exceeds {$hall->name}'s capacity of {$hall->capacity}."
                );
            }

            $specialtyHall = SpecialtyHall::create([
                'specialty_id' => $specialty->id,
                'level_id' => $specialty->level_id,
                'hall_id' => $hall->id,
                'school_branch_id' => $currentSchool->id
            ]);

            $wasUnassigned = $hall->assignment_status === 'unassigned';

            $hall->increment('num_assigned_specialties');

            if ($wasUnassigned) {
                $hall->assignment_status = 'assigned';
                $hall->save();
            }

            AdminActionEvent::dispatch([
                "permissions" => ["schoolAdmin.specialtyHall.assign"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" => [
                    'hall' => $hall->name,
                    'specialty' => $specialty->specialty_name,
                    'level' => $specialty->level->level_name,
                    'student_count' => $studentCount
                ],
                "message" => "Specialty assigned to hall successfully",
            ]);

            return $specialtyHall;
        });
    }
    public function getAvailableAssignableHalls($currentSchool, $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->find($specialtyId);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are trying to Assign Was Not Found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                null
            );
        }
        $assignedHalls = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->where("level_id", $specialty->level->id)
            ->pluck('hall_id')->toArray();

        $assignableHalls = Hall::where("school_branch_id", $currentSchool->id)
            ->whereNotIn("id", $assignedHalls)
            ->get();
        if ($assignableHalls->isEmpty()) {
            throw new AppException(
                "No Assignable Halls Left",
                404,
                "No Assignable Halls Found",
                "We Could not find any assignable halls left looks like all the halls have already been assigned"
            );
        }

        return $assignableHalls;
    }
    public function getAssignedHalls($currentSchool, $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->find($specialtyId);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are trying to Assign Was Not Found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                null
            );
        }

        $assignedHalls = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialty->id)
            ->get();
        if ($assignedHalls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned",
                404,
                "No Halls Assigned",
                "We Could not find any Halls Assigned  looks like no halls have beeen assigned to {$specialty->specialty_name}, {$specialty->level->level_name}"
            );
        }

        return $assignedHalls;
    }
    public function removeAssignedHalls($currentSchool, $specialtyHallId, $authAdmin)
    {
        return DB::transaction(function () use ($currentSchool, $specialtyHallId, $authAdmin) {

            $specialtyHall = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                ->with(['hall', 'specialty.level'])
                ->lockForUpdate()
                ->find($specialtyHallId);

            if (!$specialtyHall) {
                throw new AppException(
                    "Assigned hall not found",
                    404,
                    "Assignment Not Found",
                    "The hall assignment you are trying to remove does not exist or may have already been deleted."
                );
            }

            $hall = $specialtyHall->hall;
            if (!$hall) {
                throw new AppException("Hall missing from assignment record", 500);
            }

            $hallName      = $hall->name;
            $specialtyName = $specialtyHall->specialty?->specialty_name ?? 'Unknown';
            $levelName     = $specialtyHall->specialty?->level?->level_name ?? 'Unknown';

            $specialtyHall->delete();

            $hall->decrement('num_assigned_specialties');

            if ($hall->num_assigned_specialties <= 0) {
                $hall->num_assigned_specialties = 0;
                $hall->assignment_status = 'unassigned';
                $hall->save();
            }

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.specialtyHall.remove"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "hallManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'hall'          => $hallName,
                    'specialty'     => $specialtyName,
                    'level'         => $levelName,
                    'hall_id'       => $hall->id,
                ],
                "message" => "Specialty removed from hall successfully",
            ]);

            return true;
        });
    }
}
