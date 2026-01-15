<?php

namespace App\Services\Hall;

use App\Exceptions\AppException;
use App\Models\Hall;
use App\Events\Actions\AdminActionEvent;

class HallService
{
    public function createHall($currentSchool, $data, $authAdmin)
    {
        $existingHall = Hall::where('school_branch_id', $currentSchool->id)
            ->where('name', $data['name'])
            ->exists();

        if ($existingHall) {
            throw new AppException(
                "Hall already exists with this name",
                400,
                "Duplicate Hall",
                "You have already created a hall with the name '{$data['name']}'. Please use another name."
            );
        }

        $hall = Hall::create([
            'school_branch_id' => $currentSchool->id,
            'name'             => $data['name'],
            'capacity'         => $data['capacity'],
            'location'         => $data['location'] ?? null,
        ]);

        if (!empty($data['typeIds'])) {
            $syncData = collect($data['typeIds'])
                ->pluck('type_id')
                ->mapWithKeys(fn($typeId) => [
                    $typeId => ['school_branch_id' => $currentSchool->id],
                ])
                ->toArray();

            $hall->types()->sync($syncData);
        }

        AdminActionEvent::dispatch([
            'permissions'   => ['schoolAdmin.hall.create'],
            'roles'         => ['schoolSuperAdmin', 'schoolAdmin'],
            "action" => "schoolHall.created",
            'schoolBranch'  => $currentSchool->id,
            'feature'       => 'hallManagement',
            'authAdmin'     => $authAdmin,
            'data'          => $hall,
            'message'       => 'Hall Created',
        ]);

        return $hall;
    }
    public function updateHall($currentSchool, $updateData, $hallId, $authAdmin)
    {
        $hall = Hall::where('school_branch_id', $currentSchool->id)
            ->where('id', $hallId)
            ->first();

        if (!$hall) {
            throw new AppException(
                "Hall not found",
                404,
                "Hall Not Found",
                "The hall you are trying to update does not exist or has been deleted."
            );
        }

        if (!empty($updateData['name'])) {
            $duplicate = Hall::where('school_branch_id', $currentSchool->id)
                ->where('name', $updateData['name'])
                ->where('id', '!=', $hall->id)
                ->exists();

            if ($duplicate) {
                throw new AppException(
                    "Hall already exists with this name",
                    400,
                    "Duplicate Hall",
                    "Another hall already uses the name '{$updateData['name']}'. Please choose a different name."
                );
            }
        }

        if (!empty($updateData['typeIds'])) {
            $syncData = collect($updateData['typeIds'])
                ->pluck('type_id')
                ->mapWithKeys(fn($typeId) => [
                    $typeId => ['school_branch_id' => $currentSchool->id],
                ])
                ->toArray();

            $hall->types()->sync($syncData);
        }

        $filteredUpdateData = collect($updateData)
            ->except(['typeIds'])
            ->filter(fn($value) => !is_null($value))
            ->toArray();

        $hall->update($filteredUpdateData);

        AdminActionEvent::dispatch([
            'permissions'   => ['schoolAdmin.hall.update'],
            'roles'         => ['schoolSuperAdmin', 'schoolAdmin'],
            "action"        => "hallUpdated",
            'schoolBranch'  => $currentSchool->id,
            'feature'       => 'hallManagement',
            'authAdmin'     => $authAdmin,
            'data'          => $hall,
            'message'       => 'Hall Updated',
        ]);

        return $hall;
    }
    public function deleteHall($currentSchool, $hallId, $authAdmin)
    {
        $hall = Hall::where("school_branch_id", $currentSchool->id)
            ->find($hallId);

        if (!$hall) {
            throw new AppException(
                "Hall Not Found, it might have been deleted please try again",
                404,
                "Hall Not Found",
                "The Hall Your Trying to update was not found it might have been deleted please verify and try again"
            );
        }

        $hall->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.hall.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" =>  $hall,
                "message" => "Hall Deleted",
            ]
        );
        return $hall;
    }
    public function getAllHalls($currentSchool)
    {
        $halls = Hall::where("school_branch_id", $currentSchool->id)
            ->with(['types'])
            ->get();
        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Added",
                404,
                "No Halls Found",
                "There are no Halls available for this school branch.",
            );
        }

        return $halls;
    }
    public function getActiveHalls($currentSchool)
    {
        $halls = Hall::where("school_branch_id", $currentSchool->id)
            ->where("status", '=', "available")
            ->get();
        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Active Halls Found",
                404,
                "No Halls Active Found",
                "There are no Active Halls available for this school branch.",
            );
        }

        return $halls;
    }
    public function activateHall($currentSchool, $hallId, $authAdmin)
    {
        $hall = Hall::where("school_branch_id", $currentSchool->id)
            ->find($hallId);

        if (!$hall) {
            throw new AppException(
                "Hall Not Found, it might have been deleted please try again",
                404,
                "Hall Not Found",
                "The Hall Your Trying to update was not found it might have been deleted please verify and try again"
            );
        }

        $hall->status = 'available';
        $hall->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.hall.activated"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "action" => "hall.Deactivated",
                "authAdmin" => $authAdmin,
                "data" =>  $hall,
                "message" => "Hall Activated",
            ]
        );
        return $hall;
    }
    public function deactivateHall($currentSchool, $hallId, $authAdmin)
    {
        $hall = Hall::where("school_branch_id", $currentSchool->id)
            ->find($hallId);

        if (!$hall) {
            throw new AppException(
                "Hall Not Found, it might have been deleted please try again",
                404,
                "Hall Not Found",
                "The Hall Your Trying to update was not found it might have been deleted please verify and try again"
            );
        }

        $hall->status = 'unavailable';
        $hall->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.hall.deactivated"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "action" => "hall.Deactivated",
                "data" =>  $hall,
                "message" => "Hall Deactivated",
            ]
        );
        return $hall;
    }
    public function getHallDetails($currentSchool, $hallId)
    {
        $hall = Hall::where('school_branch_id', $currentSchool->id)
            ->where('id', $hallId)
            ->with(['types'])
            ->first();

        if (!$hall) {
            throw new AppException(
                "Hall not found",
                404,
                "Hall Not Found",
                "The hall you are trying to update does not exist or has been deleted."
            );
        }

        return $hall;
    }
}
