<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\OperationalStatService;
class OperationalStatController extends Controller
{
    protected OperationalStatService $operationalStatService;
    public function __construct(OperationalStatService $operationalStatService){
        $this->operationalStatService = $operationalStatService;
    }
    public function getSchoolOperationalStats(Request $request, $year){
        $currentSchool = $request->attributes->get('currentSchool');
        $operationalStats = $this->operationalStatService->getOperationalStats($currentSchool, $year);
        return ApiResponseService::success("Operational Stats Fetched Successfully", $operationalStats, null, 200);
    }
}
