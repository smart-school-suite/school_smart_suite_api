<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\Stats\FinancialStatistics;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class FinancialStatsController extends Controller
{
    //
    protected FinancialStatistics $financialStatistics;
    public function __construct(FinancialStatistics $financialStatistics){
        $this->financialStatistics = $financialStatistics;
    }
    public function getFinanacialStats(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getfinancialStats = $this->financialStatistics->getFinancialData($currentSchool);
        return ApiResponseService::success("Financial Report Fetched Succesfully", $getfinancialStats, null, 200);
    }
}
