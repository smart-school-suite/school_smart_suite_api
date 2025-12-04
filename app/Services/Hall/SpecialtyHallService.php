<?php

namespace App\Services\Hall;

use App\Models\Hall;
use App\Models\SpecialtyHall;
use App\Models\Specialty;
use App\Exceptions\AppException;
use App\Models\Student;
use App\Events\Actions\AdminActionEvent;

class SpecialtyHallService
{
    public function assignHallToSpecialty($currentSchool, $data, $authAdmin)
    {
        $hall = Hall::where("school_branch_id", $currentSchool->id)
            ->find($data['hall_id']);
        if (!$hall) {
            throw new AppException(
                "Hall Not Found, it might have been deleted please try again",
                404,
                "Hall Not Found",
                "The Hall Your Trying to update was not found it might have been deleted please verify and try again"
            );
        }
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->find($data["specialty_id"]);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are trying to Assign Was Not Found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                null
            );
        }

        $alreadyAssigned = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("hall_id", $data["hall_id"])
            ->where("specialty_id", $specialty->id)
            ->where("level_id", $specialty->level->id)
            ->first();

        if ($alreadyAssigned) {
            throw new AppException(
                "Specialty Already Already Assigned to this hall",
                404,
                "Specialty Already Assigned",
                "{$specialty->specialty_name} {$specialty->level->level_name} already assigned to {$hall->name}, please select another hall and try again"
            );
        }

        $studentCount = Student::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialty->id)
            ->where("level_id", $specialty->level_id)
            ->count();

        if ($studentCount > $hall->capacity) {
            throw new AppException(
                "Student Count Greater than max hall Capacity",
                404,
                "Student Count Greater than max hall capacity",
                "The Number of students {$studentCount} for {$specialty->specialty_name}, {$specialty->level->level_name}, is greater than {$hall->name} with max capacity {$hall->capacity}"
            );
        }

        $specialtyHall = SpecialtyHall::create([
            'specialty_id' => $specialty->id,
            'level_id' => $specialty->level_id,
            'hall_id' => $hall->id,
            'school_branch_id' => $currentSchool->id
        ]);

        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.specialtyHall.assign"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" =>  $hall,
                "message" => "Specialty Assigned To Hall",
            ]
        );
        return $specialtyHall;
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
        $specialtyHall = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->find($specialtyHallId);

        if ($specialtyHall) {
            throw new AppException(
                "Specialty Hall Not Found",
                404,
                "Specialty Hall Not Found",
                "We Could Not Find the HAll Assigned to this specialty it might have been deleted please try again"
            );
        }

        $specialtyHall->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.specialtyHall.remove"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" =>  $specialtyHall,
                "message" => "Specialty Removed From Hall",
            ]
        );
        return $specialtyHall;
    }
}
