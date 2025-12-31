<?php

namespace App\Services\Analytics\Operational\Widget\Teacher;

use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Services\Analytics\Operational\Aggregates\Teacher\TeacherRentensionRate as TeacherRentensionRateAggregate;
use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;

class TeacherRetentionRate
{
    protected TeacherRentensionRateAggregate $teacherRentensionRateAggregate;
    public function __construct(TeacherRentensionRateAggregate $teacherRentensionRateAggregate)
    {
        $this->teacherRentensionRateAggregate = $teacherRentensionRateAggregate;
    }

    public function getTeacherRentionRate($currentSchool){
         $targetKpis = [
             OperationalAnalyticsKpi::TEACHER_DROPOUT,
             OperationalAnalyticsKpi::TEACHER,
         ];

         $query = OperationalAnalyticQuery::base($currentSchool->id, $targetKpis);
         return $this->teacherRentensionRateAggregate->calculate($query);
    }

}
