<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\ElectionRole\BulkUpdateElectionRoleRequest;
use App\Http\Requests\ElectionRole\ConfigureOfficeTermRequest;
use App\Http\Requests\ElectionRole\CreateElectionRoleRequest;
use App\Http\Requests\ElectionRole\ElectionRoleIdRequest;
use App\Http\Requests\ElectionRole\UpdateElectionRoleRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionRoleService;
use Illuminate\Http\Request;

/**
 * Election positions and their office terms.
 */
class ElectionRoleController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionRoleService $electionRoleService) {}

    public function createElectionRole(CreateElectionRoleRequest $request)
    {
        $this->adminUser();
        $role = $this->electionRoleService->createElectionRole($request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Election Position Created Successfully", $role, null, 201);
    }

    public function updateElectionRole(UpdateElectionRoleRequest $request, string $electionRoleId)
    {
        $this->adminUser();
        $role = $this->electionRoleService->updateElectionRole($request->validated(), $electionRoleId, $this->currentSchool($request));
        return ApiResponseService::success("Election Position Updated Successfully", $role, null, 200);
    }

    public function bulkUpdateElectionRole(BulkUpdateElectionRoleRequest $request)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->bulkUpdateElectionRoles($request->validated()['election_roles'], $this->currentSchool($request));
        return ApiResponseService::success("Election Positions Updated Successfully", $roles, null, 200);
    }

    public function deleteElectionRole(Request $request, string $electionRoleId)
    {
        $this->adminUser();
        $role = $this->electionRoleService->deleteElectionRole($electionRoleId, $this->currentSchool($request));
        return ApiResponseService::success("Election Position Deleted Successfully", $role, null, 200);
    }

    public function bulkDeleteRole(ElectionRoleIdRequest $request)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->bulkDeleteElectionRoles($this->ids($request, 'electionRoleIds', 'election_role_id'), $this->currentSchool($request));
        return ApiResponseService::success("Election Positions Deleted Successfully", $roles, null, 200);
    }

    public function getAllElectionRoles(Request $request)
    {
        $roles = $this->electionRoleService->getElectionRoles($this->currentSchool($request), $request->query('election_type_id'));
        return ApiResponseService::success("Election Positions Fetched Successfully", $roles, null, 200);
    }

    public function getElectionRolesByElectionId(Request $request, string $electionId)
    {
        $roles = $this->electionRoleService->getElectionRolesByElection($this->currentSchool($request), $electionId);
        return ApiResponseService::success("Election Positions Fetched Successfully", $roles, null, 200);
    }

    public function getActiveRoles(Request $request, string $electionId)
    {
        $roles = $this->electionRoleService->getElectionRolesByElection($this->currentSchool($request), $electionId, true);
        return ApiResponseService::success("Active Election Positions Fetched Successfully", $roles, null, 200);
    }

    public function getElectionRoleDetails(Request $request, string $electionRoleId)
    {
        $role = $this->electionRoleService->getElectionRoleDetails($electionRoleId, $this->currentSchool($request));
        return ApiResponseService::success("Election Position Details Fetched Successfully", $role, null, 200);
    }

    public function activateRole(Request $request, string $electionRoleId)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->setStatus([$electionRoleId], 'active', $this->currentSchool($request));
        return ApiResponseService::success("Election Position Activated Successfully", $roles[0], null, 200);
    }

    public function deactivateRole(Request $request, string $electionRoleId)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->setStatus([$electionRoleId], 'inactive', $this->currentSchool($request));
        return ApiResponseService::success("Election Position Deactivated Successfully", $roles[0], null, 200);
    }

    public function bulkActivateRole(ElectionRoleIdRequest $request)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->setStatus($this->ids($request, 'electionRoleIds', 'election_role_id'), 'active', $this->currentSchool($request));
        return ApiResponseService::success("Election Positions Activated Successfully", $roles, null, 200);
    }

    public function bulkDeactivateRole(ElectionRoleIdRequest $request)
    {
        $this->adminUser();
        $roles = $this->electionRoleService->setStatus($this->ids($request, 'electionRoleIds', 'election_role_id'), 'inactive', $this->currentSchool($request));
        return ApiResponseService::success("Election Positions Deactivated Successfully", $roles, null, 200);
    }

    public function configureOfficeTerm(ConfigureOfficeTermRequest $request, string $electionRoleId)
    {
        $this->adminUser();
        $term = $this->electionRoleService->configureOfficeTerm($electionRoleId, $request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Office Term Configured Successfully", $term, null, 200);
    }

    public function getOfficeTerms(Request $request)
    {
        return ApiResponseService::success("Office Terms Fetched Successfully", $this->electionRoleService->getOfficeTerms($this->currentSchool($request)), null, 200);
    }
}
