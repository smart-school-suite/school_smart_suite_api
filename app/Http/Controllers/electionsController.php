<?php

namespace App\Http\Controllers;

use App\Http\Requests\ElectionType\CreateElectionTypeRequest;
use App\Http\Requests\ElectionType\UpdateElectionTypeRequest;
use App\Http\Requests\ElectionType\BulkUpdateElectionTypeRequest;
use App\Http\Requests\Election\CreateElectionRequest;
use App\Http\Requests\Election\UpdateElectionRequest;
use App\Http\Requests\Election\BulkUpdateElectionRequest;
use App\Http\Requests\Election\AddElectionParticipantsRequest;
use App\Http\Requests\Election\CreateVoteRequest;
use App\Http\Requests\Election\ElectionIdRequest;
use App\Http\Resources\ElectionCandidateResource;
use Illuminate\Support\Facades\Validator;
use App\Services\ElectionService;
use App\Services\VoteService;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;

class ElectionsController extends Controller
{
    //
    protected ElectionService $electionService;
    protected VoteService $voteService;
    public function __construct(ElectionService $electionService, VoteService $voteService)
    {
        $this->electionService = $electionService;
        $this->voteService = $voteService;
    }

    public function createElection(CreateElectionRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $election = $this->electionService->createElection($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Created Sucessfully", $election, null, 201);
    }

    //add fetch election details
    public function deleteElection(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteElection = $this->electionService->deleteElection($currentSchool, $electionId);
        return ApiResponseService::success("Election Deleted Successfully", $deleteElection, null, 200);
    }

    public function getElections(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $elections = $this->electionService->fetchElections($currentSchool);
        return ApiResponseService::success('Election fetched Sucessfully', $elections, null, 200);
    }

    public function updateElection(UpdateElectionRequest $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateElection = $this->electionService->updateElection($request->validated(), $currentSchool, $electionId);
        return ApiResponseService::success("Election Updated Successfully", $updateElection, null, 200);
    }

    public function vote(CreateVoteRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $castVote = $this->voteService->castVote($request->validated(), $currentSchool);
        return ApiResponseService::success('Voted Casted Succfully', $castVote, null, 200);
    }

    public function getElectionCandidates(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getElectionCandidates = $this->electionService->getElectionCandidates($electionId, $currentSchool);
        return ApiResponseService::success("Election Candidates Retrieved Successfully", ElectionCandidateResource::collection($getElectionCandidates), null, 200);
    }


    public function getPastElectionWinners(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getPastElectionWinners = $this->electionService->getPastElectionWinners($currentSchool);
        return ApiResponseService::success("Past Election Winners", $getPastElectionWinners, null, 200);
    }

    public function activateElectionType($electionTypeId)
    {
        $activateElectionType = $this->electionService->activateElectionType($electionTypeId);
        return ApiResponseService::success("Election Type Activated Successfully", $activateElectionType, null, 200);
    }

    public function deactivateElectionType($electionTypeId)
    {
        $deactivateElectionType = $this->electionService->deactivateElectionType($electionTypeId);
        return ApiResponseService::success("Election Type Deactivated Successfully", $deactivateElectionType, null, 200);
    }

    public function getElectionTypes(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionTypes = $this->electionService->getElectionType($currentSchool);
        return ApiResponseService::success("Election Types Fetched Successfully", $electionTypes, null, 200);
    }

    public function getActiveElectionTypes(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionType = $this->electionService->getActiveElectionType($currentSchool);
        return ApiResponseService::success("Active Election Types Fetched Successfully", $electionType, null, 200);
    }

    public function createElectionType(CreateElectionTypeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createElectionType = $this->electionService->createElectionType($request->all(), $currentSchool);
        return ApiResponseService::success("Election Type Created Successfully", $createElectionType, null, 200);
    }

    public function updateElectionType(UpdateElectionTypeRequest $request, $electionTypeId){
        $updateElectionType = $this->electionService->UpdateElectionType($request->all(), $electionTypeId);
        return ApiResponseService::success("Election Type Updated Successfully", $updateElectionType, null, 200);
    }

    public function deleteElectionType($electionTypeId){
        $deleteElectionType = $this->electionService->deleteElectionType($electionTypeId);
        return $deleteElectionType;
    }

    public function getCurrentElectionWinners(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $electionWinners = $this->electionService->getCurrentElectionWinners($currentSchool);
        return ApiResponseService::success("Current Election Winners Fetched Successfully", $electionWinners, null, 200);
    }

    public function addAllowedParticipantsByOtherElection(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $targetElectionId = $request->route('targetElectionId');
        $electionId = $request->route('electionId');
        try{
            $addAllowedParticipants = $this->electionService->addAllowedParticipantsByOtherElection($currentSchool, $electionId, $targetElectionId);
            return ApiResponseService::success("Allowed Participants Added Successfully", $addAllowedParticipants, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function getAllowedParticipants(Request $request, $electionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $allowedParticipants = $this->electionService->getAllowedElectionParticipants($currentSchool, $electionId);
        return ApiResponseService::success("Allowed Participants Fetched Successfully", $allowedParticipants, null, 200);
    }

    public function addAllowedParticipants(AddElectionParticipantsRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        try{
            $addAllowedParticipants = $this->electionService->addAllowedElectionParticipants($request->election_participants, $currentSchool);
            return ApiResponseService::success("Allowed Election Participants Added Successfully", $addAllowedParticipants, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteElection(ElectionIdRequest $request){

        try{
        $bulkDeleteElection = $this->electionService->bulkDeleteElection($request->electionIds);
        return ApiResponseService::success("Elections Deleted Successfully", $bulkDeleteElection, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateElection(BulkUpdateElectionRequest $request){
       try{
          $bulkUpdateElection = $this->electionService->bulkUpdateElection($request->elections);
          return ApiResponseService::success("Election Updated Successfully", $bulkUpdateElection, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }

    public function getUpcomingElectionsByStudent(Request $request, $studentId){
        $currentSchool = $request->attributes->get('currentSchool');
        $elections = $this->electionService->upcomingElectionByStudent($currentSchool, $studentId);
        return ApiResponseService::success("Upcoming Elections Fetched Successfully", $elections, null, 200);
    }
}
