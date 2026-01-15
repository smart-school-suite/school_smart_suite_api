<?php

namespace App\Services\Hall;

use App\Exceptions\AppException;
use App\Models\Hall;
use App\Events\Actions\AdminActionEvent;

class HallService
{
    public function createHall($currentSchool, $data, $authAdmin)
    {
        $typeCollectionIds = collect($data['typeIds']);
        $existingHall = Hall::where("school_branch_id", $currentSchool->id)
            ->where("name", $data['name'])
            ->first();


        if ($existingHall) {
            throw new AppException(
                "Hall Already Exist With This Name {$data['data']}",
                400,
                "Duplicate Hall",
                "Youve Already Created A Hall With This Same Name {$data}, use another name and try again"
            );
        }
       $hall = Hall::create([
            'school_branch_id' => $currentSchool->id,
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'location' => $data['location']
        ]);

        $hall->types()->sync($typeCollectionIds->pluck("type_id")->toArray());
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.hall.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" =>  $existingHall,
                "message" => "Hall Created",
            ]
        );
        return true;
    }

    public function updateHall($currentSchool, $updateData, $hallId, $authAdmin)
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

        $filteredUpdateData = array_filter($updateData);
        if ($updateData['name']) {
            $existingHall = Hall::where("school_branch_id", $currentSchool->id)
                ->where("name", $updateData['name'])
                ->first();
            if ($existingHall) {
                throw new AppException(
                    "Hall Already Exist With This Name {$filteredUpdateData['data']}",
                    400,
                    "Duplicate Hall",
                    "Youve Already Created A Hall With This Same Name {$filteredUpdateData}, use another name and try again"
                );
            }
        }

        if(!empty($updateData['typeIds'])){
             $hall->types()->sync(collect($updateData['typeIds'])->pluck("type_id")->toArray());
        }

        $hall->update($filteredUpdateData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.hall.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "hallManagement",
                "authAdmin" => $authAdmin,
                "data" =>  $hall,
                "message" => "Hall Updated",
            ]
        );
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
                "data" =>  $hall,
                "message" => "Hall Deactivated",
            ]
        );
        return $hall;
    }
}
