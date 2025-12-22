<?php

namespace App\Constant\Analytics\Academic;

use App\Constant\Analytics\Academic\AcademicAnalyticsDimension;
use App\Constant\Analytics\Academic\AcademicAnalyticsEvent;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class AcademicKpiDefination
{
    public static function definitions(): array
    {
        return [

            //exam kpi defination
            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_GRADE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_GRADE_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LETTER_GRADE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_RESIT_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_RESIT_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_RESIT_INCURRED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_SCORE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_SCORE_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            //student performance
            AcademicAnalyticsKpi::STUDENT_EXAM_GPA => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_GPA,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::STUDENT_EXAM_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_RESIT => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_RESIT,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_RESIT_INCURRED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_SCORE => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_SCORE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_PASS_EXAM => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_PASS_EXAM,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_FAIL_EXAM => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_FAIL_EXAM,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM_COURSE => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_COURSE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_FAILED => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_FAILED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_PASSED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::STUDENT_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            //teacher performance
            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_PASSED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::TEACHER_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_FAILED => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_FAILED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::TEACHER_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_CANDIDATE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::TEACHER_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::TEACHER_EXAM_COURSE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_GRADE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_GRADE_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::TEACHER_ID,
                    AcademicAnalyticsDimension::LETTER_GRADE_ID,
                    AcademicAnalyticsDimension::COURSE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            //exam defination
            AcademicAnalyticsKpi::EXAM => [
                'kpi' => AcademicAnalyticsKpi::EXAM,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_GPA => [
                'kpi' => AcademicAnalyticsKpi::EXAM_GPA,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::EXAM_CANDIDATE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_RESIT => [
                'kpi' => AcademicAnalyticsKpi::EXAM_CANDIDATE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_RESIT_INCURRED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_PASSED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_PASSED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_FAILED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_FAILED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_GPA_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_GPA_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_EVALUATED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_SCORE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_SCORE_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_GRADE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_GRADE_DISTRIBUTION,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::LETTER_GRADE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            //resit exam kpi

            AcademicAnalyticsKpi::RESIT_EXAM => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CREATED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_CREATED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_PASSED => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_PASSED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_PASSED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_FAILED => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_FAILED,
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::EXAM_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_FAILED
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],
        ];
    }
}
