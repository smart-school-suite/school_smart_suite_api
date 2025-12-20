<?php

namespace App\Constant\Analytics\Academic;

class AcademicKpiDefination
{
    public static function definitions(): array
    {
        return [
            //bucket academic kpis
            AcademicAnalyticsKpi::EXAM_COURSE_PASS_DISTRIBUTION => [
                'type' => 'counter',
                'aggregation' => 'bucket',
                'source_events' => [
                    'academic.exam.passed',
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE,
                    AcademicAnalyticsDimension::SPECIALTY,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ],
            ],
            AcademicAnalyticsKpi::EXAM_COURSE_FAIL_DISTRIBUTION => [
                'type' => 'counter',
                'aggregation' => 'bucket',
                'source_events' => [
                    'academic.exam.passed',
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE,
                    AcademicAnalyticsDimension::SPECIALTY,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ]
            ],
            AcademicAnalyticsKpi::EXAM_COURSE_RESIT_DISTRIBUTION => [
                'type' => 'counter',
                'aggregation' => 'bucket',
                'source_events' => [
                    'academic.exam.failed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE,
                    AcademicAnalyticsDimension::SPECIALTY,
                ]
            ],
            AcademicAnalyticsKpi::EXAM_GRADES_DISTRIBUTION => [
                'type' => 'counter',
                'aggregation' => 'bucket',
                'source_events' => [
                    'academic.examScore.summited'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_TYPE,
                    AcademicAnalyticsDimension::SPECIALTY
                ]
            ],
            AcademicAnalyticsKpi::EXAM_COURSE_SCORE_DISTRIBUTION => [
                'type' => 'counter',
                'aggregation' => 'bucket',
                'source_events' => [
                    'academic.examScore.summited'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_TYPE,
                    AcademicAnalyticsDimension::SPECIALTY
                ]
            ],

            //derived academic kpis
            AcademicAnalyticsKpi::EXAM_PASS_RATE => [
                'type' => 'derived',
                'source_events' => [],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_PASSED,
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_ASSESSED
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                    AcademicAnalyticsDimension::SPECIALTY
                ]
            ],

            AcademicAnalyticsKpi::EXAM_FAIL_RATE => [
                'type' => 'derived',
                'source_events' => [],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_ASSESSED,
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_FAILED
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                    AcademicAnalyticsDimension::SPECIALTY
                ]
            ],

            AcademicAnalyticsKpi::COURSE_PASS_RATE => [
                'type' => 'derived',
                'source_events' => [],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_ASSESSED,
                    AcademicAnalyticsKpi::COURSE_STUDENTS_PASSED
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE,
                    AcademicAnalyticsDimension::EXAM,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ]
            ],

            AcademicAnalyticsKpi::COURSE_FAIL_RATE => [
                'type' => 'derived',
                'source_events' => [],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_ASSESSED,
                    AcademicAnalyticsKpi::COURSE_STUDENT_FAILED
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE,
                    AcademicAnalyticsDimension::EXAM,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ]
            ],
            //school academic kpis

            AcademicAnalyticsKpi::SCHOOL_AVERAGE_SCORE => [
                'type' => 'derived',
                'source_events' => [
                    'candidate.evaluated'
                ],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_COUNT,
                    AcademicAnalyticsKpi::AVERAGE_EXAM_TOTAL_SCORE
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                ]
            ],

            AcademicAnalyticsKpi::SCHOOL_AVERAGE_GPA => [
                'type' => 'derived',
                'source_events' => [
                    'candidate.evaluated'
                ],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_COUNT,
                    AcademicAnalyticsKpi::AVERAGE_EXAM_GPA
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                ]
            ],

            AcademicAnalyticsKpi::SCHOOL_PASS_RATE => [
                'type' => 'derived',
                'source_events' => [
                    'candidate.evaluated'
                ],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_COUNT,
                    AcademicAnalyticsKpi::EXAM_PASS_RATE
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                ]
            ],

            AcademicAnalyticsKpi::SCHOOL_FAIL_RATE => [
                'type' => 'derived',
                'source_events' => [
                    'candidate.evaluated'
                ],
                'depends_on' => [
                    AcademicAnalyticsKpi::EXAM_COUNT,
                    AcademicAnalyticsKpi::EXAM_FAIL_RATE
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::ACADEMIC_YEAR,
                ]
            ],

            //COUNTER
            AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_ASSESSED => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'exam.candidate.created'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM,
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ]
            ],

            AcademicAnalyticsKpi::EXAM_COUNT => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'exam.created'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_TYPE,
                ]
            ],

            AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_FAILED => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'exam.candidate.failed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM, //count by exam e.g first semester software engineering exam
                    AcademicAnalyticsDimension::EXAM_TYPE, //count by exam type e.g first semester exam
                ]
            ],

            AcademicAnalyticsKpi::EXAM_TOTAL_STUDENTS_PASSED => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'exam.candidate.passed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM, //count by exam e.g first semester software engineering exam
                    AcademicAnalyticsDimension::EXAM_TYPE, //count by exam type e.g first semester exam
                ]
            ],

            AcademicAnalyticsKpi::EXAM_TOTAL_NUMBER_OF_RESITS => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'exam.candidate.failed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM, //count by exam e.g first semester software engineering exam
                    AcademicAnalyticsDimension::EXAM_TYPE, //count by exam type e.g first semester exam
                ]
            ],

            AcademicAnalyticsKpi::COURSE_STUDENTS_PASSED => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'course.passed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM, //count by exam e.g first semester software engineering exam
                    AcademicAnalyticsDimension::EXAM_TYPE, //count by exam type e.g first semester exam
                ]
            ],

            AcademicAnalyticsKpi::COURSE_STUDENT_FAILED => [
                'type' => 'counter',
                'aggregation' => 'count',
                'source_events' => [
                    'course.failed'
                ],
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM, //count by exam e.g first semester software engineering exam
                    AcademicAnalyticsDimension::EXAM_TYPE, //count by exam type e.g first semester exam
                ]
            ]
        ];
    }
}
