<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Election\ElectionRoleService;
use App\Services\ApiResponseService;
use App\Http\Requests\ElectionRole\ElectionRoleIdRequest;
use App\Http\Requests\ElectionRole\CreateElectionRoleRequest;
use App\Http\Requests\ElectionRole\UpdateElectionRoleRequest;
use App\Http\Requests\ElectionRole\BulkUpdateElectionRoleRequest;
use App\Http\Resources\ElectionRoleResource;

class ElectionRoleController extends Controller
{
    protected ElectionRoleService $electionRoleService;
    public function __construct(ElectionRoleService $electionRoleService)
    {
        $this->electionRoleService = $electionRoleService;
    }

    public function createElectionRole(CreateElectionRoleRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $createElectionRole = $this->electionRoleService->createElectionRole($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Role Created Sucessfully", $createElectionRole, null, 201);
    }

    public function updateElectionRole(UpdateElectionRoleRequest $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $updateElectionRole = $this->electionRoleService->updateElectionRole($request->validated(), $currentSchool, $electionRoleId);
        return ApiResponseService::success("Election Role Updated Succefully", $updateElectionRole, null, 200);
    }

    public function deleteElectionRole(Request $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElectionRole = $this->electionRoleService->deleteElectionRole($electionRoleId, $currentSchool);
        return ApiResponseService::success("Election Role Deleted Sucessfully", $deleteElectionRole, null, 200);
    }

    public function getElectionRolesByElectionId(Request $request, string $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionResults = $this->electionRoleService->getElectionRolesByElection($currentSchool, $electionId);
        return ApiResponseService::success('Election Roles Fetched Sucessfully', $electionResults, null, 200);
    }

    public function getAllElectionRoles(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionRoles = $this->electionRoleService->getAllElectionRoles($currentSchool);
        return ApiResponseService::success('Election Roles Fetched Succesfully', ElectionRoleResource::collection($electionRoles), null, 200);
    }

    public function bulkDeleteRole(ElectionRoleIdRequest $request)
    {
        $bulkDeleteRole = $this->electionRoleService->bulkDeleteElectionRole($request->electionRoleIds);
        return ApiResponseService::success("Election Roles Deleted Successfully", $bulkDeleteRole, null, 200);
    }

    public function bulkUpdateElectionRole(BulkUpdateElectionRoleRequest $request)
    {

        $bulkUpdateElectionRole = $this->electionRoleService->bulkUpdateElectionRole($request->election_roles);
        return ApiResponseService::success("Election Roles Updated Successfully", $bulkUpdateElectionRole, null, 200);
    }

    public function activateRole($electionRoleId)
    {
        $activateRole = $this->electionRoleService->activateRole($electionRoleId);
        return ApiResponseService::success("Election Role Activated Successfully", $activateRole, null, 200);
    }

    public function bulkActivateRole(ElectionRoleIdRequest $request)
    {
        $bulkActivate = $this->electionRoleService->bulkActivateElectionRole($request->electionRoleIds);
        return ApiResponseService::success("Election Roles Activated Successfully", $bulkActivate, null, 200);
    }

    public function deactivateRole($electionRoleId)
    {
        $deactivateRole = $this->electionRoleService->deactivateRole($electionRoleId);
        return ApiResponseService::success("Election Roles Deactivated Successfully", $deactivateRole, null, 200);
    }

    public function bulkDeactivateRole(ElectionRoleIdRequest $request)
    {
        $bulkActivate = $this->electionRoleService->bulkDeactivateRole($request->electionRoleIds);
        return ApiResponseService::success("Election Roles Deactivated Successfully", $bulkActivate, null, 200);
    }
    public function getActiveRoles(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getActiveRole = $this->electionRoleService->getActiveRoles($currentSchool, $electionId);
        return ApiResponseService::success("Active Roles Fetch Successfully", $getActiveRole, null, 200);
    }

    public function getElectionRoleDetails(Request $request, $electionRoleId){
          $currentSchool = $request->attributes->get("currentSchool");
          $electionRoleDetails = $this->electionRoleService->getElectionRoleDetails($electionRoleId, $currentSchool);
          return ApiResponseService::success("Election Role Details Fetched Successfully", $electionRoleDetails, null, 200);
    }
}
