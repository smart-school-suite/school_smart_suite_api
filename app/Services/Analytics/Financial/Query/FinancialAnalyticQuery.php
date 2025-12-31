<?php

namespace App\Services\Analytics\Financial\Query;

use App\Models\Analytics\Finance\FinanceAnalyticSnapshot;

class FinancialAnalyticQuery
{
    public static function base(string $schoolBranchId, int $year, array $kpis)
    {
        return  FinanceAnalyticSnapshot::where("school_branch_id", $schoolBranchId)
            ->where("year", $year)
            ->whereIn("kpi", $kpis)
            ->get();
    }
}
