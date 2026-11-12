<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\Election\PublishElectionResultsRequest;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionResultService;
use Illuminate\Http\Request;

class ElectionResultController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionResultService $resultService) {}

    /** Live results for admins, published results for everyone. */
    public function getElectionResults(Request $request, string $electionId)
    {
        $results = $this->resultService->getResults($electionId, $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Election Results Fetched Successfully", $results, null, 200);
    }

    public function publishResults(PublishElectionResultsRequest $request, string $electionId)
    {
        $this->adminUser();
        $results = $this->resultService->publishResults($electionId, $this->currentSchool($request), $request->tieResolutions());
        return ApiResponseService::success("Election Results Published Successfully", $results, null, 200);
    }

    public function getElectionHistory(Request $request)
    {
        $history = $this->resultService->getElectionHistory($this->currentSchool($request), $request->query('election_type_id'));
        return ApiResponseService::success("Election History Fetched Successfully", $history, null, 200);
    }
}
