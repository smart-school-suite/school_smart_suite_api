<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\OperationalStatService;
use App\Services\Analytics\OperationalAnalyticsService;
class OperationalStatController extends Controller
{
    protected OperationalStatService $operationalStatService;
    protected OperationalAnalyticsService $operationalAnalyticsService;
    public function __construct(
        OperationalStatService $operationalStatService,
        OperationalAnalyticsService $operationalAnalyticsService
        ){
        $this->operationalStatService = $operationalStatService;
        $this->operationalAnalyticsService = $operationalAnalyticsService;
    }
    public function getSchoolOperationalStats(Request $request, $year){
        $currentSchool = $request->attributes->get('currentSchool');
        $operationalStats = $this->operationalAnalyticsService->getOperationalAnalytics($currentSchool, $year);
        return ApiResponseService::success("Operational Stats Fetched Successfully", $operationalStats, null, 200);
    }
}
