<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\ElectionCandidate\ElectionCandidateIdRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionCandidateService;
use Illuminate\Http\Request;

class ElectionCandidateController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionCandidateService $candidateService) {}

    public function getElectionCandidates(Request $request)
    {
        $candidates = $this->candidateService->getCandidates($this->currentSchool($request), $request->only(['election_id', 'election_role_id', 'status']));
        return ApiResponseService::success("Election Candidates Fetched Successfully", $candidates, null, 200);
    }

    public function getCandidatesByElection(Request $request, string $electionId)
    {
        $filters = array_merge($request->only(['election_role_id', 'status']), ['election_id' => $electionId]);
        return ApiResponseService::success("Election Candidates Fetched Successfully", $this->candidateService->getCandidates($this->currentSchool($request), $filters), null, 200);
    }

    /** Active candidates grouped by position, for the voting screen. */
    public function getBallot(Request $request, string $electionId)
    {
        return ApiResponseService::success("Election Ballot Fetched Successfully", $this->candidateService->getBallot($this->currentSchool($request), $electionId), null, 200);
    }

    public function getElectionCandidateDetails(Request $request, string $candidateId)
    {
        return ApiResponseService::success("Election Candidate Details Fetched Successfully", $this->candidateService->getCandidateDetails($this->currentSchool($request), $candidateId), null, 200);
    }

    public function disqualifyCandidate(Request $request, string $candidateId)
    {
        $this->adminUser();
        $candidates = $this->candidateService->disqualifyCandidates($this->currentSchool($request), [$candidateId], $request->input('reason'));
        return ApiResponseService::success("Candidate Disqualified Successfully", $candidates[0], null, 200);
    }

    public function reinstateCandidate(Request $request, string $candidateId)
    {
        $this->adminUser();
        $candidates = $this->candidateService->reinstateCandidates($this->currentSchool($request), [$candidateId]);
        return ApiResponseService::success("Candidate Reinstated Successfully", $candidates[0], null, 200);
    }

    public function bulkDisqualifyCandidate(ElectionCandidateIdRequest $request)
    {
        $this->adminUser();
        $candidates = $this->candidateService->disqualifyCandidates($this->currentSchool($request), $this->ids($request, 'candidateIds', 'candidate_id'), $request->validated()['reason'] ?? null);
        return ApiResponseService::success("Candidates Disqualified Successfully", $candidates, null, 200);
    }

    public function bulkReinstateCandidate(ElectionCandidateIdRequest $request)
    {
        $this->adminUser();
        $candidates = $this->candidateService->reinstateCandidates($this->currentSchool($request), $this->ids($request, 'candidateIds', 'candidate_id'));
        return ApiResponseService::success("Candidates Reinstated Successfully", $candidates, null, 200);
    }
}
