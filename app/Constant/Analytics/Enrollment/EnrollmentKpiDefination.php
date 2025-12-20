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
                'kpi' => 'student_enrollments',
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                    EnrollmentAnalyticsDimension::SOURCE_ID,
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
                'kpi' => 'student_enrollment_source',
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::SOURCE_ID,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_GENDER_ENROLLMENTS => [
                'kpi' => 'student_gender_enrollments',
                'type' => 'counter',
                'dimensions' => [
                    EnrollmentAnalyticsDimension::GENDER_ID,
                    EnrollmentAnalyticsDimension::DEPARTMENT_ID,
                    EnrollmentAnalyticsDimension::LEVEL_ID,
                    EnrollmentAnalyticsDimension::SPECIALTY_ID,
                ],
                'source_events' => [
                    EnrollmentAnalyticsEvent::STUDENT_ENROLLED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            EnrollmentAnalyticsKpi::STUDENT_DROPOUT => [
                'kpi' => 'student_dropout',
                'type' => 'counter',
                'dimensions' => [
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
