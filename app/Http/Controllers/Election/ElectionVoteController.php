<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\Election\CastVoteRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionVoteService;
use Illuminate\Http\Request;

class ElectionVoteController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionVoteService $voteService) {}

    public function castVote(CastVoteRequest $request)
    {
        $result = $this->voteService->castVote($request->validated()['election_id'], $request->ballot(), $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Vote Cast Successfully", $result, null, 201);
    }

    public function getMyBallotStatus(Request $request, string $electionId)
    {
        $status = $this->voteService->getVoterBallotStatus($electionId, $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Ballot Status Fetched Successfully", $status, null, 200);
    }
}
