<?php

namespace App\Services\Analytics\Operational\Widget\Teacher;

use App\Services\Analytics\Operational\Aggregates\Teacher\TeacherStudentRatio as TeacherStudentRatioAggregate;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;

class TeacherStudentRatio
{
    protected TeacherStudentRatioAggregate $teacherStudentRatioAggregate;
    public function __construct(TeacherStudentRatioAggregate $teacherStudentRatioAggregate)
    {
        $this->teacherStudentRatioAggregate = $teacherStudentRatioAggregate;
    }

    public function getTeacherStudentRatio($currentSchool)
    {
        $targetOperationalKpis = [
            OperationalAnalyticsKpi::TEACHER
        ];
        $targetEnrollmentKpis = [
            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS,
            EnrollmentAnalyticsKpi::STUDENT_DROPOUT
        ];

        $operationalQuery = OperationalAnalyticQuery::base($currentSchool->id, $targetOperationalKpis);
        $enrollmentQuery = EnrollmentAnalyticQuery::base($currentSchool->id, $targetEnrollmentKpis);

        return $this->teacherStudentRatioAggregate->calculate($enrollmentQuery, $operationalQuery);
    }
}
