<?php

namespace App\Http\Controllers\ReportAnalytics;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\FinancialReportAnalytics;
use Illuminate\Http\Request;

class FinancialreportController extends Controller
{
    //

    protected FinancialReportAnalytics $financialReportAnalytics;
    public function __construct(FinancialReportAnalytics $financialReportAnalytics){
        $this->financialReportAnalytics = $financialReportAnalytics;
    }

    public function getFinanacialStats(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getfinancialStats = $this->financialReportAnalytics->getFinancialData($currentSchool);
        return ApiResponseService::success("Financial Report Fetched Succesfully", $getfinancialStats, null, 200);
    }

}
