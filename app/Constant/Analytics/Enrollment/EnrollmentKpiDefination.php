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

            EnrollmentAnalyticsKpi::STUDENT_DEPARTMENT_ENROLLMENTS => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_DEPARTMENT_ENROLLMENTS,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_SPECIALTY_ENROLLMENTS => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_SPECIALTY_ENROLLMENTS,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,

                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_ENROLLEMENT_SOURCE => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_ENROLLEMENT_SOURCE,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SOURCE_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_DEPARTMENT_ENROLLMENT_SOURCE => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_DEPARTMENT_ENROLLMENT_SOURCE,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SOURCE_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => false,
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_SPECIALTY_ENROLLMENT_SOURCE => [
                'kpi' => EnrollmentAnalyticsKpi::STUDENT_SPECIALTY_ENROLLMENT_SOURCE,
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SOURCE_ID,
                    EnrollmentAnalyticsDimension::SCHOOL_BRANCH_ID,
                    EnrollmentAnalyticsDimension::YEAR,
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

            EnrollmentAnalyticsKpi::STUDENT_GENDER_DROPOUT => [
                'kpi' => 'student_gender_dropout',
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::GENDER_ID,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
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
