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
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $createElectionRole = $this->electionRoleService->createElectionRole($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Role Created Sucessfully", $createElectionRole, null, 201);
    }

    public function updateElectionRole(UpdateElectionRoleRequest $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $updateElectionRole = $this->electionRoleService->updateElectionRole($request->validated(), $currentSchool, $electionRoleId, $authAdmin);
        return ApiResponseService::success("Election Role Updated Succefully", $updateElectionRole, null, 200);
    }

    public function deleteElectionRole(Request $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deleteElectionRole = $this->electionRoleService->deleteElectionRole($electionRoleId, $currentSchool, $authAdmin);
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
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkDeleteRole = $this->electionRoleService->bulkDeleteElectionRole($request->electionRoleIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Roles Deleted Successfully", $bulkDeleteRole, null, 200);
    }

    public function bulkUpdateElectionRole(BulkUpdateElectionRoleRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkUpdateElectionRole = $this->electionRoleService->bulkUpdateElectionRole($request->election_roles, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Roles Updated Successfully", $bulkUpdateElectionRole, null, 200);
    }

    public function activateRole(Request $request, $electionRoleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $activateRole = $this->electionRoleService->activateRole($electionRoleId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Role Activated Successfully", $activateRole, null, 200);
    }

    public function bulkActivateRole(ElectionRoleIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkActivate = $this->electionRoleService->bulkActivateElectionRole($request->electionRoleIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Roles Activated Successfully", $bulkActivate, null, 200);
    }

    public function deactivateRole(Request $request, $electionRoleId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deactivateRole = $this->electionRoleService->deactivateRole($electionRoleId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Roles Deactivated Successfully", $deactivateRole, null, 200);
    }

    public function bulkDeactivateRole(ElectionRoleIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkActivate = $this->electionRoleService->bulkDeactivateRole($request->electionRoleIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Election Roles Deactivated Successfully", $bulkActivate, null, 200);
    }
    public function getActiveRoles(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getActiveRole = $this->electionRoleService->getActiveRoles($currentSchool, $electionId);
        return ApiResponseService::success("Active Roles Fetch Successfully", $getActiveRole, null, 200);
    }

    public function getElectionRoleDetails(Request $request, $electionRoleId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionRoleDetails = $this->electionRoleService->getElectionRoleDetails($electionRoleId, $currentSchool);
        return ApiResponseService::success("Election Role Details Fetched Successfully", $electionRoleDetails, null, 200);
    }

    public function getStudentElectionRoles(Request $request, $electionId)
    {
        $student = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $getElectionRoles = $this->electionRoleService->getStudentElectionRoles($currentSchool, $student, $electionId);
        return ApiResponseService::success("Election Roles Fetched Successfully", $getElectionRoles, null, 200);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
