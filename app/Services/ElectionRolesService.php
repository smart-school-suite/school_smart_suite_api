<?php

namespace App\Services;

use App\Models\ElectionRoles;
use App\Models\Elections;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ElectionRolesService
{
    // Implement your logic here

    public function createElectionRole(array $data, $currentSchool)
    {
        DB::beginTransaction();
        $electionRole = new ElectionRoles();
        $electionRole->name = $data["name"];
        $electionRole->description = $data["description"];
        $electionRole->election_type_id = $data["election_type_id"];
        $electionRole->school_branch_id = $currentSchool->id;
        $electionRole->save();
        Role::create([
            'name' => $data["name"],
            'guard_name' => 'student',
            'school_branch_id' => $currentSchool->id,
        ]);
        DB::commit();
        return $electionRole;
    }
    public function updateElectionRole(array $data, $currentSchool, $electionRoleId)
    {
        $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($electionRoleId);
        if (!$electionRole) {
            return ApiResponseService::error("Election Role Not found", null, 404);
        }

        $filteredData = array_filter($data);
        $electionRole->update($filteredData);
        if ($filteredData['name']) {
            $role = Role::where('name', $electionRole->name)->where('school_branch_id', $currentSchool->id)->first();
            if ($role) {
                $role->name = $filteredData['name'];
                $role->save();
            } else {
                Role::create([
                    'name' => $filteredData['name'],
                    'guard_name' => 'student',
                    'school_branch_id' => $currentSchool->id,
                ]);
            }
        }
        return $electionRole;
    }
    public function bulkUpdateElectionRole(array $UpdateElectionList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($UpdateElectionList as $UpdateElection) {
                $electionRole = ElectionRoles::findOrFail($UpdateElection['election_role_id']);
                $filteredData = array_filter($UpdateElection);
                $electionRole->update($filteredData);
                $result[] = $electionRole;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteElectionRole($electionRoleId, $currentSchool)
    {
        $electionRole = ElectionRoles::where("school_branch_id", $currentSchool->id)->find($electionRoleId);
        if (!$electionRole) {
            return ApiResponseService::error("Election Role Not found", null, 404);
        }
        $electionRole->delete();
        $role = Role::where('name', $electionRole->name)->where('school_branch_id', $currentSchool->id)->first();
        if ($role) {
            $role->delete();
        }
        return $electionRole;
    }
    public function bulkDeleteElectionRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionRoleIds as $electionRoleId) {
                $electionRole = ElectionRoles::findOrFail($electionRoleId['election_role_id']);
                $electionRole->delete();
                $role = Role::where('name', $electionRole->name)->where('school_branch_id', $electionRole->school_branch_id)->first();
                if ($role) {
                    $role->delete();
                }
                $result[] = $electionRole;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getElectionRole($currentSchool, $electionId)
    {
        $election = Elections::findOrFail($electionId);
        $electionRoles = ElectionRoles::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->with(['electionType'])
            ->get();
        return $electionRoles;
    }
    public function getAllElectionRoles($currentSchool)
    {
        $electionRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
            ->with(['electionType'])
            ->get();
        return $electionRoles;
    }
    public function activateRole($electionRoleId)
    {
        $electionRole = ElectionRoles::findOrFail($electionRoleId);
        $electionRole->status = 'active';
        $electionRole->save();
        return $electionRole;
    }
    public function bulkActivateElectionRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionRoleIds as $electionRoleId) {
                $electionRole = ElectionRoles::findOrFail($electionRoleId['election_role_id']);
                $electionRole->status = 'active';
                $electionRole->save();
                $result[] = $electionRole;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getActiveRoles($currentSchool, $electionId)
    {
        $election = Elections::findOrFail($electionId);
        $activeRoles = ElectionRoles::where("school_branch_id", $currentSchool->id)
            ->where("election_type_id", $election->election_type_id)
            ->get();
        return $activeRoles;
    }
    public function deactivateRole($electionRoleId)
    {
        $electionRole = ElectionRoles::findOrFail($electionRoleId);
        $electionRole->status = 'inactive';
        $electionRole->save();
        return $electionRole;
    }
    public function bulkDeactivateRole($electionRoleIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($electionRoleIds as $electionRoleId) {
                $electionRole = ElectionRoles::findOrFail($electionRoleId['election_role_id']);
                $electionRole->status = 'inactive';
                $electionRole->save();
                $result[] = $electionRole;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
