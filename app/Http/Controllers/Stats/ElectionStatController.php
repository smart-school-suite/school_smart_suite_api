<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\ElectionStatService;
class ElectionStatController extends Controller
{
    protected ElectionStatService $electionStatService;
    public function __construct(ElectionStatService $electionStatService){
        $this->electionStatService = $electionStatService;
    }

    public function getElectionStats(Request $request, $year){
        $currentSchool = $request->attributes->get('currentSchool');
        $electionStats = $this->electionStatService->getElectionStats($currentSchool, $year);
        return ApiResponseService::success("Election Stats Fetched Successfully", $electionStats, null, 200);
    }
}
