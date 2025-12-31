<?php

namespace App\Services\Analytics\Operational\Query;
use App\Models\Analytics\Enrollment\EnrollmentAnalyticSnapshot;
class EnrollmentAnalyticQuery
{
    public static function base(string $schoolBranchId, array $kpis)
    {
        return  EnrollmentAnalyticSnapshot::where("school_branch_id", $schoolBranchId)
            ->whereIn("kpi", $kpis)
            ->get();
    }
}
