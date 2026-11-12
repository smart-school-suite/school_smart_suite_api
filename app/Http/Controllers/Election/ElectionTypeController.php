<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\ElectionType\BulkUpdateElectionTypeRequest;
use App\Http\Requests\ElectionType\CreateElectionTypeRequest;
use App\Http\Requests\ElectionType\ElectionTypeIdRequest;
use App\Http\Requests\ElectionType\UpdateElectionTypeRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionTypeService;
use Illuminate\Http\Request;

class ElectionTypeController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionTypeService $electionTypeService) {}

    public function createElectionType(CreateElectionTypeRequest $request)
    {
        $this->adminUser();
        $type = $this->electionTypeService->createElectionType($request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Election Type Created Successfully", $type, null, 201);
    }

    public function updateElectionType(UpdateElectionTypeRequest $request, string $electionTypeId)
    {
        $this->adminUser();
        $type = $this->electionTypeService->updateElectionType($request->validated(), $electionTypeId, $this->currentSchool($request));
        return ApiResponseService::success("Election Type Updated Successfully", $type, null, 200);
    }

    public function bulkUpdateElectionType(BulkUpdateElectionTypeRequest $request)
    {
        $this->adminUser();
        $types = $this->electionTypeService->bulkUpdateElectionTypes($request->validated()['election_types'], $this->currentSchool($request));
        return ApiResponseService::success("Election Types Updated Successfully", $types, null, 200);
    }

    public function deleteElectionType(Request $request, string $electionTypeId)
    {
        $this->adminUser();
        $type = $this->electionTypeService->deleteElectionType($electionTypeId, $this->currentSchool($request));
        return ApiResponseService::success("Election Type Deleted Successfully", $type, null, 200);
    }

    public function getElectionType(Request $request)
    {
        return ApiResponseService::success("Election Types Fetched Successfully", $this->electionTypeService->getElectionTypes($this->currentSchool($request)), null, 200);
    }

    public function getActiveElectionType(Request $request)
    {
        return ApiResponseService::success("Active Election Types Fetched Successfully", $this->electionTypeService->getActiveElectionTypes($this->currentSchool($request)), null, 200);
    }

    public function getElectionTypeDetails(Request $request, string $electionTypeId)
    {
        return ApiResponseService::success("Election Type Details Fetched Successfully", $this->electionTypeService->getElectionTypeDetails($electionTypeId, $this->currentSchool($request)), null, 200);
    }

    public function activateElectionType(Request $request, string $electionTypeId)
    {
        $this->adminUser();
        $types = $this->electionTypeService->setStatus([$electionTypeId], 'active', $this->currentSchool($request));
        return ApiResponseService::success("Election Type Activated Successfully", $types[0], null, 200);
    }

    public function deactivateElectionType(Request $request, string $electionTypeId)
    {
        $this->adminUser();
        $types = $this->electionTypeService->setStatus([$electionTypeId], 'inactive', $this->currentSchool($request));
        return ApiResponseService::success("Election Type Deactivated Successfully", $types[0], null, 200);
    }

    public function bulkActivateElectionType(ElectionTypeIdRequest $request)
    {
        $this->adminUser();
        $types = $this->electionTypeService->setStatus($this->ids($request, 'electionTypeIds', 'election_type_id'), 'active', $this->currentSchool($request));
        return ApiResponseService::success("Election Types Activated Successfully", $types, null, 200);
    }

    public function bulkDeactivateElectionType(ElectionTypeIdRequest $request)
    {
        $this->adminUser();
        $types = $this->electionTypeService->setStatus($this->ids($request, 'electionTypeIds', 'election_type_id'), 'inactive', $this->currentSchool($request));
        return ApiResponseService::success("Election Types Deactivated Successfully", $types, null, 200);
    }
}
