<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Election\ElectionService;
use App\Http\Requests\Election\CreateElectionRequest;
use App\Http\Requests\Election\UpdateElectionRequest;
use App\Http\Resources\ElectionResource;
use App\Services\ApiResponseService;

class ElectionController extends Controller
{
    protected ElectionService $electionService;
    public function __construct(ElectionService $electionService)
    {
        $this->electionService = $electionService;
    }

    public function createElection(CreateElectionRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $election = $this->electionService->createElection($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Created Sucessfully", $election, null, 201);
    }

    public function getElectionDetails(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionDetails = $this->electionService->getElectionDetails($currentSchool, $electionId);
        return ApiResponseService::success("Election Details Fetched Successfully", $electionDetails, null, 200);
    }
    public function deleteElection(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElection = $this->electionService->deleteElection($currentSchool, $electionId);
        return ApiResponseService::success("Election Deleted Successfully", $deleteElection, null, 200);
    }

    public function getElections(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $elections = $this->electionService->getElections($currentSchool);
        return ApiResponseService::success('Election fetched Sucessfully', ElectionResource::collection($elections), null, 200);
    }

    public function updateElection(UpdateElectionRequest $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateElection = $this->electionService->updateElection($request->validated(), $currentSchool, $electionId);
        return ApiResponseService::success("Election Updated Successfully", $updateElection, null, 200);
    }

    public function getPastElections(Request $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $pastElectionResults = $this->electionService->getPastElection($currentSchool);
         return ApiResponseService::success("Past Elections Fetched Successfully", ElectionResource::collection( $pastElectionResults), null, 200);
    }
}
