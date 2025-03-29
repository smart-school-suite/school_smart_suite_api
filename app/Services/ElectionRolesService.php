<?php

namespace App\Services;

use App\Models\ElectionRoles;

class ElectionRolesService
{
    // Implement your logic here

    public function createElectionRole(array $data, $currentSchool)
    {
        $electionRole = new ElectionRoles();
        $electionRole->name = $data["name"];
        $electionRole->description = $data["description"];
        $electionRole->election_id = $data["election_id"];
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

    public function deleteElectionRole($election_role_id, $currentSchool)
    {
        $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($election_role_id);
        if (!$electionRole) {
            return ApiResponseService::error("Election Role Not found", null, 404);
        }
        $electionRole->delete();
        return $electionRole;
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
}
