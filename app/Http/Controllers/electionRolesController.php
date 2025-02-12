<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\ElectionRolesService;
use App\Http\Requests\ElectionRolesRequest;
use App\Http\Requests\UpdateElectionRolesRequest;
use Illuminate\Http\Request;

class electionRolesController extends Controller
{
    //
    protected ElectionRolesService $electionRolesService;
    public function __construct(ElectionRolesService $electionRolesService)
    {
        $this->electionRolesService = $electionRolesService;
    }
    public function createElectionRole(ElectionRolesRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $createElectionRole = $this->electionRolesService->createElectionRole($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Role Created Sucessfully", $createElectionRole, null, 201);
    }

    public function updateElectionRole(UpdateElectionRolesRequest $request, string $election_role_id)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $updateElectionRole = $this->electionRolesService->updateElectionRole($request->validated(), $currentSchool, $election_role_id);
        return ApiResponseService::success("Election Role Updated Succefully", $updateElectionRole, null, 200);
    }

    public function deleteElectionRole(Request $request, string $election_role_id)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElectionRole = $this->electionRolesService->deleteElectionRole($election_role_id, $currentSchool);
        return ApiResponseService::success("Election Role Deleted Sucessfully", $deleteElectionRole, null, 200);
    }

    public function getElectionRoles(Request $request, string $election_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionResults = $this->electionRolesService->getElectionRole($currentSchool, $election_id);
        return ApiResponseService::success('Election Roles Fetched Sucessfully', $electionResults, null, 200);
    }
}
