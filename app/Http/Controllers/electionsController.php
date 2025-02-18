<?php

namespace App\Http\Controllers;


use App\Http\Requests\UpdateElectionRequest;
use App\Http\Requests\VoteRequest;
use App\Http\Requests\ElectionRequest;
use App\Services\ElectionService;
use App\Services\VoteService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class electionsController extends Controller
{
    //
    protected ElectionService $electionService;
    protected VoteService $voteService;
    public function __construct(ElectionService $electionService, VoteService $voteService)
    {
        $this->electionService = $electionService;
        $this->voteService = $voteService;
    }

    public function createElection(ElectionRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $election = $this->electionService->createElection($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Created Sucessfully", $election, null, 201);
    }

    public function deleteElection(Request $request, $election_id)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElection = $this->electionService->deleteElection($currentSchool, $election_id);
        return ApiResponseService::success("Election Results Fetched Sucessfully", $deleteElection, null, 200);
    }

    public function getElections(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $elections = $this->electionService->fetchElections($currentSchool);
        return ApiResponseService::success('Election fetched Sucessfully', $elections, null, 200);
    }

    public function updateElection(UpdateElectionRequest $request, $election_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateElection = $this->electionService->updateElection($request->validated(), $currentSchool, $election_id);
        return ApiResponseService::success("Election Updated Successfully", $updateElection, null, 200);
    }

    public function vote(VoteRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $castVote = $this->voteService->castVote($request->validated(), $currentSchool);
        return ApiResponseService::success('Voted Casted Succfully', $castVote, null, 200);
    }

    public function getElectionCandidates(Request $request, $electionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getElectionCandidates = $this->electionService->getElectionCandidates($electionId, $currentSchool);
        return ApiResponseService::success("Election Candidates Retrieved Successfully", $getElectionCandidates, null, 200);
    }
}
