<?php

namespace App\Services\Analytics;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Analytics\Academic\AcademicAnalyticSnapshot;
class AcademicAnalyticsService
{
    public function getAcademicAnalytics($currentSchool, $year){

    }
    protected static function averageGpa($currentSchool, $year){
        $stats = AcademicAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
                    ->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                    ->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)
                    ->where("year", $year)
                    ->get();
        $gpa = $stats->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)->pluck('value');
        $candidate = $stats->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)->pluck('value');
        $averageGpa = $gpa / $candidate;
        return $averageGpa;
    }

    protected static function totalExams($currentSchool, $year){
        $totalExams = AcademicAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
                        ->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM)
                        ->where("year", $year)
                        ->pluck('value')
                        ->first();
         return $totalExams;
    }

    protected static function totalEvaluatedStudents($currentSchool, $year){
        $totalEvalutated = AcademicAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
                                  ->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                                  ->where("year", $year)
                                  ->pluck('value')
                                  ->first();
        return $totalEvalutated;
    }
}
