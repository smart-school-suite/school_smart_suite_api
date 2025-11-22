<?php

namespace App\Services\Hall;

use App\Exceptions\AppException;
use App\Models\Hall;

class HallService
{
    public function createHall($currentSchool, $data)
    {
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
        Hall::create([
            'school_branch_id' => $currentSchool->id,
            'is_exam_hall' => $data['is_exam_hall'],
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'location' => $data['location']
        ]);

        return true;
    }

    public function updateHall($currentSchool, $updateData, $hallId)
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

        $hall->update($filteredUpdateData);
        return $hall;
    }

    public function deleteHall($currentSchool, $hallId){
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
        return $hall;
    }


    public function getAllHalls($currentSchool){
         $halls = Hall::where("school_branch_id", $currentSchool->id)
                    ->get();
         if($halls->isEmpty()){
            throw new AppException(
                "No Halls Added",
                404,
                "No Halls Found",
                "There are no Halls available for this school branch.",
            );
         }

         return $halls;
    }

    public function getActiveHalls($currentSchool){
          $halls = Hall::where("school_branch_id", $currentSchool->id)
                  ->where("status", '=', "available")
                    ->get();
         if($halls->isEmpty()){
            throw new AppException(
                "No Halls Active Halls Found",
                404,
                "No Halls Active Found",
                "There are no Active Halls available for this school branch.",
            );
         }

        return $halls;
    }

    public function activateHall($currentSchool, $hallId){
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
        return $hall;
    }

    public function deactivateHall($currentSchool, $hallId){
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
        return $hall;
    }



}
