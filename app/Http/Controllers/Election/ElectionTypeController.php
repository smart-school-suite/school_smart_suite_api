<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectionType\CreateElectionTypeRequest;
use App\Http\Requests\ElectionType\ElectionTypeIdRequest;
use App\Http\Requests\ElectionType\UpdateElectionTypeRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Election\ElectionTypeService;
class ElectionTypeController extends Controller
{
    protected ElectionTypeService $electionTypeService;
    public function __construct(ElectionTypeService $electionTypeService){
         $this->electionTypeService = $electionTypeService;
    }

    public function createElectionType(CreateElectionTypeRequest $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $this->electionTypeService->createElectionType($request->validated(), $currentSchool);
         return ApiResponseService::success("Election Type Created Successfully", null, null, 200);
    }

    public function updateElectionType(UpdateElectionTypeRequest $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $this->electionTypeService->UpdateElectionType($request->validated(), $currentSchool);
         return ApiResponseService::success("Election Type Updated Successfully", null, null, 200);
    }

    public function deleteElectionType(Request $request, $electionTypeId){
         $currentSchool = $request->attributes->get("currentSchool");
         $this->electionTypeService->deleteElectionType($electionTypeId, $currentSchool);
         return ApiResponseService::success("Election Type Deleted Successfully", null, null, 200);
    }

    public function getElectionType(Request $request){
       $currentSchool = $request->attributes->get("currentSchool");
       $electionTypes = $this->electionTypeService->getElectionType($currentSchool);
       return ApiResponseService::success("Election Type Fetched Successfully", $electionTypes, null, 200);
    }

    public function activateElectionType(Request $request, $electionTypeId){
        $currentSchool = $request->attributes->get("currentSchool");
        $this->electionTypeService->activateElectionType( $electionTypeId, $currentSchool);
        return ApiResponseService::success("Election Type Activated Successfully", null, null, 200);
    }

    public function deactivateElectionType(Request $request, $electionTypeId){
         $currentSchool = $request->attributes->get("currentSchool");
         $this->electionTypeService->deactivateElectionType($currentSchool, $electionTypeId);
         return ApiResponseService::success("Election Type Deactivated Successfully", null, null, 200);
    }

    public function getActiveElectionType(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $electionTypes = $this->electionTypeService->getActiveElectionType($currentSchool);
        return ApiResponseService::success("Active Election Types Fetched Successfully", $electionTypes, null,  200);
    }

    public function bulkActivateElectionType(ElectionTypeIdRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $this->electionTypeService->bulkActivateElectionType($currentSchool, $request->electionTypeIds);
        return ApiResponseService::success("Election Types Activated Successfully", null, null, 200);
    }

    public function bulkDeactivateElectionType(ElectionTypeIdRequest $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $this->electionTypeService->bulkDeactivateElectionType($currentSchool, $request->electionTypeIds);
        return ApiResponseService::success("Election Types Deactivated Successfully", null, null, 200);
    }

    public function getElectionTypeDetails(Request $request, $electionTypeId){
        $currentSchool = $request->attributes->get("currentSchool");
        $this->electionTypeService->getElectionTypeDetails($electionTypeId, $currentSchool);
        return ApiResponseService::success("Election Type Details Fetched Successfully", null, null, 200);
    }
}
