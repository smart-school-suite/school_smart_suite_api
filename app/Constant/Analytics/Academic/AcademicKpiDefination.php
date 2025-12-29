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

            // Course Performance KPIs
            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE,
                'name' => 'Exam Course Candidates Count',
                'description' => 'The total number of students (candidates) registered and evaluated for a specific course in a specific exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED,
                'name' => 'Exam Course Candidates Passed',
                'description' => 'The number of students who passed a specific course in a specific exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED,
                'name' => 'Exam Course Candidates Failed',
                'description' => 'The number of students who failed a specific course in a specific exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_GRADE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_GRADE_DISTRIBUTION,
                'name' => 'Grade Distribution per Course Exam',
                'description' => 'Distribution of grades (e.g., A, B, C, D, F) achieved by students in a specific course exam.',
                'type' => 'distribution',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::LETTER_GRADE_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_GRADE,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_COURSE_RESIT_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::EXAM_COURSE_RESIT_DISTRIBUTION,
                'name' => 'Resit Distribution per Course Exam',
                'description' => 'Number of students required to resit a specific course exam (i.e., those who initially failed).',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
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
                'name' => 'Score Distribution per Course Exam',
                'description' => 'Distribution of raw scores or percentage marks obtained by students in a specific course exam.',
                'type' => 'distribution',
                'dimensions' => [
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_SCORE,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            // Student Performance KPIs
            AcademicAnalyticsKpi::STUDENT_EXAM_GPA => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_GPA,
                'name' => 'Student Exam GPA',
                'description' => 'The Grade Point Average (GPA) achieved by a student in a specific exam session.',
                'type' => 'value',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_GPA_CALCULATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM,
                'name' => 'Student Exams Taken',
                'description' => 'Total number of exams a student has participated in.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_RESIT => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_RESIT,
                'name' => 'Student Resits',
                'description' => 'Number of resit exams a student has taken or is required to take.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_RESIT_INCURRED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_PASS_EXAM => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_PASS_EXAM,
                'name' => 'Student Exams Passed',
                'description' => 'Total number of exams passed by a student.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
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
                'name' => 'Student Exams Failed',
                'description' => 'Total number of exams failed by a student.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_PASSED,
                'name' => 'Student Course Exams Passed',
                'description' => 'Number of course exams passed by a student in a specific exam session.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::STUDENT_EXAM_COURSE_FAILED => [
                'name' => 'Student Course Exams Failed',
                'description' => 'Number of course exams failed by a student in a specific exam session.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::STUDENT_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            // Teacher Performance KPIs
            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_PASSED => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_PASSED,
                'name' => 'Teacher Courses Passed Count',
                'description' => 'Number of students who passed courses taught by a specific teacher in an exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::TEACHER_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
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
                'name' => 'Teacher Courses Failed Count',
                'description' => 'Number of students who failed courses taught by a specific teacher in an exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::TEACHER_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
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
                'name' => 'Teacher Course Candidates',
                'description' => 'Number of students evaluated in courses taught by a specific teacher in an exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::TEACHER_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_EVALUATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_GRADE_DISTRIBUTION => [
                'kpi' => AcademicAnalyticsKpi::TEACHER_EXAM_COURSE_GRADE_DISTRIBUTION,
                'name' => 'Teacher Course Grade Distribution',
                'description' => 'Distribution of grades in courses taught by a specific teacher.',
                'type' => 'distribution',
                'dimensions' => [
                    AcademicAnalyticsDimension::TEACHER_ID,
                    AcademicAnalyticsDimension::COURSE_ID,
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::LETTER_GRADE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_COURSE_GRADE,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            // Exam-Level Performance KPIs
            AcademicAnalyticsKpi::EXAM_GPA => [
                'kpi' => AcademicAnalyticsKpi::EXAM_GPA,
                'name' => 'Overall Exam GPA',
                'description' => 'Average GPA across all students in a specific exam session.',
                'type' => 'value',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::SPECIALTY_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_GPA_CALCULATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::EXAM_CANDIDATE,
                'name' => 'Exam Candidates Total',
                'description' => 'Total number of students registered for a specific exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::DEPARTMENT_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::EXAM_PASSED => [
                'kpi' => AcademicAnalyticsKpi::EXAM_PASSED,
                'name' => 'Exam Passed Count',
                'description' => 'Number of students who passed all courses in a specific exam (overall success).',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
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
                'name' => 'Exam Failed Count',
                'description' => 'Number of students who failed one or more courses in a specific exam.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE,
                'name' => 'Resit Exam Candidates',
                'description' => 'Number of students registered for resit exams.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_CREATED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_PASSED => [
                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_PASSED,
                'name' => 'Resit Exams Passed',
                'description' => 'Number of students who passed their resit exams.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_PASSED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],

            AcademicAnalyticsKpi::RESIT_EXAM_FAILED => [

                'kpi' => AcademicAnalyticsKpi::RESIT_EXAM_FAILED,
                'name' => 'Resit Exams Failed',
                'description' => 'Number of students who still failed after taking resit exams.',
                'type' => 'counter',
                'dimensions' => [
                    AcademicAnalyticsDimension::EXAM_ID,
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::RESIT_EXAM_CANDIDATE_FAILED,
                ],
                'time_series' => [
                    'enabled' => true,
                    'granularities' => ['year'],
                ],
            ],
            AcademicAnalyticsKpi::RESIT => [
                'kpi' => AcademicAnalyticsKpi::RESIT,
                'name' => 'Resit Exams Failed',
                'type' => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                'source_events' => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_RESIT_INCURRED,
                ],
            ],
            //school academic stats
            AcademicAnalyticsKpi::SCHOOL_EXAM => [
                "kpi" => AcademicAnalyticsKpi::SCHOOL_EXAM,
                "type" => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    AcademicAnalyticsEvent::EXAM_CREATED
                ],
                "time_series" => [
                    "enabled" => true,
                    "granularities" => ["year"]
                ]
            ],
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE => [
                "kpi" => AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
                "type" => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_EVALUATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED => [
                "kpi" =>  AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED,
                "type" => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_FAILED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED => [
                "kpi" => AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED,
                "type" => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_PASSED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
            AcademicAnalyticsKpi::SCHOOL_GPA => [
                "kpi" => AcademicAnalyticsKpi::SCHOOL_GPA,
                "type" => "counter",
                "dimensions" => [
                    AcademicAnalyticsDimension::SCHOOL_BRANCH_ID,
                    AcademicAnalyticsDimension::YEAR,
                    AcademicAnalyticsDimension::EXAM_TYPE_ID,
                    AcademicAnalyticsDimension::LEVEL_ID
                ],
                "source_events" => [
                    AcademicAnalyticsEvent::EXAM_CANDIDATE_GPA_CALCULATED
                ],
                "time_series" => [
                    "enabled" => false
                ]
            ],
        ];
    }
}
