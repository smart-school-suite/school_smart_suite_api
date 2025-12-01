<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Election\ElectionService;
use App\Http\Requests\Election\CreateElectionRequest;
use App\Http\Requests\Election\UpdateElectionRequest;
use App\Http\Resources\ElectionResource;
use App\Services\ApiResponseService;
use App\Http\Requests\Election\AddElectionParticipantsRequest;
use Exception;

class ElectionController extends Controller
{
    protected ElectionService $electionService;
    public function __construct(ElectionService $electionService)
    {
        $this->electionService = $electionService;
    }

    public function createElection(CreateElectionRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $election = $this->electionService->createElection($request->validated(), $currentSchool, $authAdmin);
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
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deleteElection = $this->electionService->deleteElection($currentSchool, $electionId, $authAdmin);
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
        $authAdmin = $this->resolveUser();
        $updateElection = $this->electionService->updateElection($request->validated(), $currentSchool, $electionId, $authAdmin);
        return ApiResponseService::success("Election Updated Successfully", $updateElection, null, 200);
    }

    public function getPastElections(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $pastElectionResults = $this->electionService->getPastElection($currentSchool);
        return ApiResponseService::success("Past Elections Fetched Successfully", ElectionResource::collection($pastElectionResults), null, 200);
    }

    public function getUpcomingElectionsByStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $student = $this->resolveUser();
        $elections = $this->electionService->upcomingElectionByStudent($currentSchool, $student);
        return ApiResponseService::success("Upcoming Elections Fetched Successfully", $elections, null, 200);
    }

    public function addAllowedParticipantsByOtherElection(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $targetElectionId = $request->route('targetElectionId');
        $electionId = $request->route('electionId');
        $addAllowedParticipants = $this->electionService->addAllowedParticipantsByOtherElection($currentSchool, $electionId, $targetElectionId, $authAdmin);
        return ApiResponseService::success("Allowed Participants Added Successfully", $addAllowedParticipants, null, 200);
    }

    public function getAllowedParticipants(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $allowedParticipants = $this->electionService->getAllowedElectionParticipants($currentSchool, $electionId);
        return ApiResponseService::success("Allowed Participants Fetched Successfully", $allowedParticipants, null, 200);
    }

    public function addAllowedParticipants(AddElectionParticipantsRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $addAllowedParticipants = $this->electionService->addAllowedElectionParticipants($request->election_participants, $currentSchool, $authAdmin);
        return ApiResponseService::success("Allowed Election Participants Added Successfully", $addAllowedParticipants, null, 200);
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
