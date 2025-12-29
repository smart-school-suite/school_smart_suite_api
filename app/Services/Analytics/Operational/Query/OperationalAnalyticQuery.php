<?php

namespace App\Services\Analytics\Operational\Query;

use App\Models\Analytics\Operational\OperationalAnalyticSnapshot;

class OperationalAnalyticQuery
{
    public static function base(string $schoolBranchId, array $kpis)
    {
        return  OperationalAnalyticSnapshot::where("school_branch_id", $schoolBranchId)
            ->whereIn("kpi", $kpis)
            ->get();
    }
}
