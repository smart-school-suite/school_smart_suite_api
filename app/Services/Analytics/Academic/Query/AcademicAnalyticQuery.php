<?php

namespace App\Services\Analytics\Academic\Query;
use App\Models\Analytics\Academic\AcademicAnalyticSnapshot;
class AcademicAnalyticQuery
{
   public static function base(string $schoolBranchId, int $year, array $kpis){
      return  AcademicAnalyticSnapshot::where("school_branch_id", $schoolBranchId)
                                   ->where("year", $year)
                                   ->whereIn("kpi", $kpis)
                                   ->get();
   }
}
