<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\FinancialStatService;
use App\Services\Analytics\FinancialAnalyticsService;

class FinancialStatController extends Controller
{
    protected FinancialStatService $financialStatService;
    protected FinancialAnalyticsService $financialAnalyticsService;

    public function __construct(FinancialStatService $financialStatService, FinancialAnalyticsService $financialAnalyticsService)
    {
        $this->financialStatService = $financialStatService;
        $this->financialAnalyticsService = $financialAnalyticsService;
    }

    public function getFinancialStats(Request $request,  int  $year)
    {
        $currentSchool = $request->attributes->get('currentSchool');
         $financialAnalyticsStats = $this->financialAnalyticsService->getFinanceAnalyticsStats($currentSchool, $year);
        return ApiResponseService::success("Financial Stats Fetched Successfully", $financialAnalyticsStats, null, 200);
    }
}
