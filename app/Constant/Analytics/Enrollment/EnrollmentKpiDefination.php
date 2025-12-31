<?php

namespace App\Constant\Analytics\Enrollment;

use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsDimension;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsKpi;
use App\Constant\Analytics\Enrollment\EnrollmentAnalyticsEvent;

class EnrollmentKpiDefination
{
    public static function definitions(): array
    {
        return [

            EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_ENROLLMENTS,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_ENROLLEMENT_SOURCE => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_ENROLLEMENT_SOURCE,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SOURCE_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_DROPOUT => [
                'kpi' => 'student_dropout',
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_DROPPED_OUT,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],


        ];
    }
}
