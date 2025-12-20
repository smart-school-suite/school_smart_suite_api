<?php

namespace App\Constant\Analytics\Academic;

class AcademicAnalyticsKpi
{
    // === Student Performance ===
    public const STUDENT_TOTAL_SCORE = 'student_total_score';
    public const STUDENT_COURSES_TAKEN = 'student_courses_taken';
    public const STUDENT_COURSES_PASSED = 'student_courses_passed';
    public const STUDENT_GPA = 'student_gpa';
    public const STUDENT_INCREASE_PERFORMANCE = "student_increase_performance";
    public const STUDENT_DECREASE_PERFORMANCE = "student_decrease_performance";
    public const STUDENT_PASS_RATE = 'student_pass_rate';
    public const STUDENT_FAIL_RATE = 'student_fail_rate';
    public const STUDENT_RESIT_POTENTIAL = 'student_resit_potential';
    //public const STUDENT_

    // === Teacher Performance ===
    public const TEACHER_TOTAL_SCORE = 'teacher_total_score';
    public const TEACHER_STUDENTS_TAUGHT = 'teacher_students_taught';
    public const TEACHER_STUDENTS_PASSED = 'teacher_students_passed';
    public const TEACHER_PASS_RATE = 'teacher_pass_rate';

    // === Course Analytics ===
    public const COURSE_TOTAL_SCORE = 'course_total_score';
    public const COURSE_STUDENTS_PASSED = 'course_students_passed';
    public const COURSE_STUDENT_FAILED = 'course_students_failed';
    public const COURSE_PASS_RATE = 'course_pass_rate';
    public consT COURSE_FAIL_RATE = 'course_fail_rate';

    // === School Academic Health ===
    public const SCHOOL_AVERAGE_SCORE = 'school_average_score';
    public const SCHOOL_PASS_RATE = 'school_pass_rate';
    public const SCHOOL_AVERAGE_GPA = 'school_average_gpa';
    public const SCHOOL_FAIL_RATE = 'school_pass_rate';

    // === Exam Participation ===
    public const EXAM_TOTAL_STUDENTS_ASSESSED = 'exam_total_students_assessed';

    // === Exam Outcomes ===
    public const EXAM_TOTAL_STUDENTS_PASSED = 'exam_total_students_passed';
    public const EXAM_TOTAL_STUDENTS_FAILED = 'exam_total_students_failed';

    // === Exam Rates ===
    public const EXAM_PASS_RATE = 'exam_pass_rate';
    public const EXAM_FAIL_RATE = 'exam_fail_rate';

    // === Exam Scores ===
    public const AVERAGE_EXAM_TOTAL_SCORE = 'average_exam_total_score';
    public const AVERAGE_EXAM_GPA = 'average_exam_gpa';

    // === Distributions ===
    public const EXAM_COURSE_FAIL_DISTRIBUTION = 'exam_course_fail_distribution';
    public const EXAM_COURSE_PASS_DISTRIBUTION = 'exam_course_pass_distribution';
    public const EXAM_COURSE_RESIT_DISTRIBUTION = 'exam_course_resit_distribution';
    public const EXAM_GRADES_DISTRIBUTION = 'exam_grades_distribution';
    public const EXAM_COURSE_SCORE_DISTRIBUTION = 'exam_course_score_distribution';

    // === Resits ===
    public const EXAM_TOTAL_NUMBER_OF_RESITS = 'exam_total_number_of_resits';
    public const EXAM_COUNT = 'total_number_exams';

}
