<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\FinancialStatService;
class FinancialStatController extends Controller
{
    protected FinancialStatService $financialStatService;
    public function __construct(FinancialStatService $financialStatService){
        $this->financialStatService = $financialStatService;
    }

    public function getFinancialStats(Request $request,  int  $year){
        $currentSchool = $request->attributes->get('currentSchool');
        $financialStats = $this->financialStatService->getFinancialStats($currentSchool, $year);
        return ApiResponseService::success("Financial Stats Fetched Successfully", $financialStats, null, 200);
    }
}
