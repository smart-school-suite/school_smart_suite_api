<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\ElectionResultsService;
use Illuminate\Http\Request;

class electionResultsController extends Controller
{
    //
    protected ElectionResultsService $electionResultsService;
    public function __construct(ElectionResultsService $electionResultsService){
        $this->electionResultsService = $electionResultsService;
    }
    public function fetchElectionResults(Request $request)
    {
        $election_id = $request->route('election_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $electionResults = $this->electionResultsService->fetchElectionResults($election_id, $currentSchool);
         return ApiResponseService::success("Election Results Fetched Sucessfully", $electionResults, null, 200);
    }

}
