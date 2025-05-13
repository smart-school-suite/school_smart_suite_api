<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\ElectionResultsService;
use Illuminate\Http\Request;

class ElectionResultsController extends Controller
{
    //
    protected ElectionResultsService $electionResultsService;
    public function __construct(ElectionResultsService $electionResultsService){
        $this->electionResultsService = $electionResultsService;
    }
    public function getElectionResults(Request $request)
    {
        $electionId = $request->route('electionId');
        $currentSchool = $request->attributes->get('currentSchool');
        $electionResults = $this->electionResultsService->fetchElectionResults($electionId, $currentSchool);
         return ApiResponseService::success("Election Results Fetched Sucessfully", $electionResults, null, 200);
    }

}
