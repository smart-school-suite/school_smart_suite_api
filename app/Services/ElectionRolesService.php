<?php

namespace App\Services;

use App\Models\ElectionRoles;
use App\Models\Elections;
use Exception;
use Illuminate\Support\Facades\DB;

class ElectionRolesService
{
    // Implement your logic here

    public function createElectionRole(array $data, $currentSchool)
    {
        $electionRole = new ElectionRoles();
        $electionRole->name = $data["name"];
        $electionRole->description = $data["description"];
        $electionRole->election_type_id = $data["election_type_id"];
        $electionRole->school_branch_id = $currentSchool->id;
        $electionRole->save();
        return $electionRole;
    }

    public function updateElectionRole(array $data, $currentSchool, $election_role_id)
    {
        $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($election_role_id);
        if (!$electionRole) {
            return ApiResponseService::error("Election Role Not found", null, 404);
        }

        $filteredData = array_filter($data);
        $electionRole->update($filteredData);
        return $electionRole;
    }

    public function bulkUpdateElectionRole(array $UpdateElectionList){
        $result = [];
        try{
            DB::beginTransaction();
           foreach($UpdateElectionList as $UpdateElection){
              $electionRole = ElectionRoles::findOrFail($UpdateElection['election_role_id']);
              $filteredData = array_filter($UpdateElection);
              $electionRole->update($filteredData);
              $result[] = [
                 $electionRole
              ];
           }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteElectionRole($election_role_id, $currentSchool)
    {
        $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($election_role_id);
        if (!$electionRole) {
            return ApiResponseService::error("Election Role Not found", null, 404);
        }
        $electionRole->delete();
        return $electionRole;
    }

    public function bulkDeleteElectionRole($electionRoleIds){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($electionRoleIds as $electionRoleId){
               $electionRole = ElectionRoles::findOrFail($electionRoleId);
               $electionRole->delete();
               $result[] = [
                $electionRole
               ];
            }
            DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }


    public function getElectionRole($currentSchool, $election_id)
    {
        $electionRoles = ElectionRoles::where('school_branch_id', $currentSchool->id)
            ->where('election_id', $election_id)
            ->with(['election'])
            ->get();
        return $electionRoles;
    }

    public function getAllElectionRoles($currentSchool){
        $electionRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
                         ->with(['election'])
                         ->get();
        return $electionRoles;
    }

    public function activateRole( $electionRoleId){
        $electionRole = ElectionRoles::findOrFail($electionRoleId);
        $electionRole->status = 'active';
        $electionRole->save();
        return $electionRole;
    }

    public function bulkActivateElectionRole($electionRoleIds){
         $result = [];
         try{
            DB::beginTransaction();
            foreach($electionRoleIds as $electionRoleId){
                $electionRole = ElectionRoles::findOrFail($electionRoleId);
                $electionRole->status = 'active';
                $electionRole->save();
                $result[] = [
                    $electionRole
                ];
            }
            DB::commit();
           return $result;
         }
         catch(Exception $e){
            DB::rollBack();
            throw $e;
         }
    }

    public function getActiveRoles($currentSchool, $electionId){
        $election = Elections::findOrFail($electionId);
        $activeRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
                                      ->where("election_type_id", $election->election_type_id)
                                      ->get();
        return $activeRoles;
    }

    public function deactivateRole($electionRoleId){
        $electionRole = ElectionRoles::findOrFail($electionRoleId);
        $electionRole->status = 'inactive';
        $electionRole->save();
        return $electionRole;
    }

    public function bulkDeactivateRole($electionRoleIds){
        $result = [];
        try{
           DB::beginTransaction();
           foreach($electionRoleIds as $electionRoleId){
               $electionRole = ElectionRoles::findOrFail($electionRoleId);
               $electionRole->status = 'inactive';
               $electionRole->save();
               $result[] = [
                   $electionRole
               ];
           }
           DB::commit();
          return $result;
        }
        catch(Exception $e){
           DB::rollBack();
           throw $e;
        }
    }
}
