<?php

namespace App\Services\Analytics\Operational\Widget\Teacher;

use App\Services\Analytics\Operational\Aggregates\Teacher\TeacherStudentRatioLevel as TeacherStudentRatioLevelAggregate;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Services\Analytics\Operational\Query\EnrollmentAnalyticQuery;
use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;

class TeacherStudentRatioLevel
{
    protected  TeacherStudentRatioLevelAggregate $teacherStudentRatioLevelAggregate;
    public function __construct(TeacherStudentRatioLevelAggregate $teacherStudentRatioLevelAggregate)
    {
        $this->teacherStudentRatioLevelAggregate = $teacherStudentRatioLevelAggregate;
    }

    public function getTeacherStudentLevelRatio($currentSchool)
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

        return $this->teacherStudentRatioLevelAggregate->calculate($enrollmentQuery, $operationalQuery);
    }
}
