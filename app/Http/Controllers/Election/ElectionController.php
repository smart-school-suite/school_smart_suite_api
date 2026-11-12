<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\Election\BulkUpdateElectionRequest;
use App\Http\Requests\Election\ConfigureElectionParticipantsRequest;
use App\Http\Requests\Election\CreateElectionRequest;
use App\Http\Requests\Election\ElectionIdRequest;
use App\Http\Requests\Election\UpdateElectionRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionParticipantService;
use App\Services\Election\ElectionService;
use Illuminate\Http\Request;

class ElectionController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(
        protected ElectionService $electionService,
        protected ElectionParticipantService $participantService,
    ) {}

    public function createElection(CreateElectionRequest $request)
    {
        $this->adminUser();
        $election = $this->electionService->createElection($request->validated(), $this->currentSchool($request));
        return ApiResponseService::success("Election Created Successfully", $election, null, 201);
    }

    public function updateElection(UpdateElectionRequest $request, string $electionId)
    {
        $this->adminUser();
        $election = $this->electionService->updateElection($request->validated(), $electionId, $this->currentSchool($request));
        return ApiResponseService::success("Election Updated Successfully", $election, null, 200);
    }

    public function bulkUpdateElection(BulkUpdateElectionRequest $request)
    {
        $this->adminUser();
        $elections = $this->electionService->bulkUpdateElections($request->validated()['elections'], $this->currentSchool($request));
        return ApiResponseService::success("Elections Updated Successfully", $elections, null, 200);
    }

    public function deleteElection(Request $request, string $electionId)
    {
        $this->adminUser();
        $election = $this->electionService->deleteElection($electionId, $this->currentSchool($request));
        return ApiResponseService::success("Election Deleted Successfully", $election, null, 200);
    }

    public function bulkDeleteElection(ElectionIdRequest $request)
    {
        $this->adminUser();
        $elections = $this->electionService->bulkDeleteElections($this->ids($request, 'electionIds', 'election_id'), $this->currentSchool($request));
        return ApiResponseService::success("Elections Deleted Successfully", $elections, null, 200);
    }

    public function getElections(Request $request)
    {
        $elections = $this->electionService->getElections($this->currentSchool($request), $request->only(['status', 'election_type_id', 'system_academic_year_id']));
        return ApiResponseService::success("Elections Fetched Successfully", $elections, null, 200);
    }

    public function getPastElections(Request $request)
    {
        return ApiResponseService::success("Past Elections Fetched Successfully", $this->electionService->getPastElections($this->currentSchool($request)), null, 200);
    }

    public function getElectionDetails(Request $request, string $electionId)
    {
        return ApiResponseService::success("Election Details Fetched Successfully", $this->electionService->getElectionDetails($electionId, $this->currentSchool($request)), null, 200);
    }

    /** Elections the signed-in student / teacher / admin may vote or apply in. */
    public function getMyElections(Request $request)
    {
        $elections = $this->electionService->getParticipantElections($this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Elections Fetched Successfully", $elections, null, 200);
    }

    // ---------------------------------------------------------- lifecycle

    public function publishElection(Request $request, string $electionId)
    {
        $this->adminUser();
        $election = $this->electionService->publishElection($electionId, $this->currentSchool($request));
        return ApiResponseService::success("Election Published Successfully", $election, null, 200);
    }

    public function cancelElection(Request $request, string $electionId)
    {
        $this->adminUser();
        $election = $this->electionService->cancelElection($electionId, $this->currentSchool($request));
        return ApiResponseService::success("Election Cancelled Successfully", $election, null, 200);
    }

    public function closeVoting(Request $request, string $electionId)
    {
        $this->adminUser();
        $election = $this->electionService->closeVoting($electionId, $this->currentSchool($request));
        return ApiResponseService::success("Voting Closed Successfully", $election, null, 200);
    }

    // ------------------------------------------------------- participants

    public function configureParticipants(ConfigureElectionParticipantsRequest $request, string $electionId)
    {
        $this->adminUser();
        $summary = $this->participantService->configureParticipants($this->currentSchool($request), $electionId, $request->validated());
        return ApiResponseService::success("Election Participants Configured Successfully", $summary, null, 200);
    }

    public function refreshParticipants(Request $request, string $electionId)
    {
        $this->adminUser();
        $summary = $this->participantService->refreshParticipants($this->currentSchool($request), $electionId);
        return ApiResponseService::success("Election Participants Refreshed Successfully", $summary, null, 200);
    }

    public function copyParticipants(Request $request, string $electionId, string $targetElectionId)
    {
        $this->adminUser();
        $summary = $this->participantService->copyParticipantsFromElection($this->currentSchool($request), $electionId, $targetElectionId);
        return ApiResponseService::success("Election Participants Copied Successfully", $summary, null, 200);
    }

    public function getParticipants(Request $request, string $electionId)
    {
        $participants = $this->participantService->getParticipants(
            $this->currentSchool($request),
            $electionId,
            $request->only(['participant_type', 'can_vote', 'can_apply', 'per_page'])
        );
        return ApiResponseService::success("Election Participants Fetched Successfully", $participants, null, 200);
    }
}
