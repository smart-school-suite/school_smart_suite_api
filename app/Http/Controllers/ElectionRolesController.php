<?php

namespace App\Http\Controllers;

use App\Http\Requests\ElectionRole\ElectionRoleIdRequest;
use App\Services\ApiResponseService;
use App\Services\ElectionRolesService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ElectionRole\CreateElectionRoleRequest;
use App\Http\Requests\ElectionRole\UpdateElectionRoleRequest;
use App\Http\Requests\ElectionRole\BulkUpdateElectionRoleRequest;
use App\Http\Resources\ElectionRoleResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ElectionRolesController extends Controller
{
    //
    protected ElectionRolesService $electionRolesService;
    public function __construct(ElectionRolesService $electionRolesService)
    {
        $this->electionRolesService = $electionRolesService;
    }
    public function createElectionRole(CreateElectionRoleRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $createElectionRole = $this->electionRolesService->createElectionRole($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Role Created Sucessfully", $createElectionRole, null, 201);
    }

    public function updateElectionRole(UpdateElectionRoleRequest $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $updateElectionRole = $this->electionRolesService->updateElectionRole($request->validated(), $currentSchool, $electionRoleId);
        return ApiResponseService::success("Election Role Updated Succefully", $updateElectionRole, null, 200);
    }

    public function deleteElectionRole(Request $request, string $electionRoleId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElectionRole = $this->electionRolesService->deleteElectionRole($electionRoleId, $currentSchool);
        return ApiResponseService::success("Election Role Deleted Sucessfully", $deleteElectionRole, null, 200);
    }

    public function getElectionRolesByElectionId(Request $request, string $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionResults = $this->electionRolesService->getElectionRoles($currentSchool, $electionId);
        return ApiResponseService::success('Election Roles Fetched Sucessfully', $electionResults, null, 200);
    }

    public function getAllElectionRoles(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $electionRoles = $this->electionRolesService->getAllElectionRoles($currentSchool);
        return ApiResponseService::success('Election Roles Fetched Succesfully', ElectionRoleResource::collection($electionRoles), null, 200);
    }

    public function bulkDeleteRole(ElectionRoleIdRequest $request){
        try{
           $bulkDeleteRole = $this->electionRolesService->bulkDeleteElectionRole($request->electionRoleIds);
           return ApiResponseService::success("Election Roles Deleted Successfully", $bulkDeleteRole, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateElectionRole(BulkUpdateElectionRoleRequest $request){
          try{
             $bulkUpdateElectionRole = $this->electionRolesService->bulkUpdateElectionRole($request->election_roles);
             return ApiResponseService::success("Election Roles Updated Successfully", $bulkUpdateElectionRole, null, 200);
          }
          catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
          }
    }

    public function activateRole($electionRoleId){
        $activateRole = $this->electionRolesService->activateRole($electionRoleId);
        return ApiResponseService::success("Election Role Activated Successfully", $activateRole, null, 200);
    }

    public function bulkActivateRole(ElectionRoleIdRequest $request){
        try{
           $bulkActivate = $this->electionRolesService->bulkActivateElectionRole($request->electionRoleIds);
           return ApiResponseService::success("Election Roles Activated Successfully", $bulkActivate, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function deactivateRole($electionRoleId){
        $deactivateRole = $this->electionRolesService->deactivateRole($electionRoleId);
        return ApiResponseService::success("Election Roles Deactivated Successfully", $deactivateRole, null, 200);
    }

    public function bulkDeactivateRole(ElectionRoleIdRequest $request){
        try{
            $bulkActivate = $this->electionRolesService->bulkDeactivateRole($request->electionRoleIds);
            return ApiResponseService::success("Election Roles Deactivated Successfully", $bulkActivate, null, 200);
         }
         catch(Exception $e){
             return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }
    public function getActiveRoles(Request $request, $electionId){
        $currentSchool = $request->attributes->get("currentSchool");
        $getActiveRole = $this->electionRolesService->getActiveRoles($currentSchool, $electionId);
        return ApiResponseService::success("Active Roles Fetch Successfully", $getActiveRole, null, 200);
    }
}
