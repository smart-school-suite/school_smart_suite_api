<?php

namespace App\Constant\Analytics\Academic;

class AcademicAnalyticsKpi
{
    //tracks how many people took a particular course

    //course performance
   public const EXAM_COURSE_CANDIDATE = "exam_course_candidate_count";
   public const EXAM_COURSE_CANDIDATE_PASSED = "exam_course_candidate_pass";
   public const EXAM_COURSE_CANDIDATE_FAILED = "exam_course_cadidate_failed";
   public const EXAM_COURSE_GRADE_DISTRIBUTION = "exam_course_grade_distribution";
   public const EXAM_COURSE_RESIT_DISTRIBUTION = "exam_course_resit_distribution";
   public const EXAM_COURSE_SCORE_DISTRIBUTION = "exam_course_score_distribution";

   //student performance
   public const STUDENT_EXAM_GPA = "student_exam_gpa";
   public const STUDENT_EXAM = 'student_exam';
   public const STUDENT_RESIT = 'student_resit';
   public const STUDENT_SCORE = 'student_score';
   public const STUDENT_PASS_EXAM = 'student_pass_exam';
   public const STUDENT_FAIL_EXAM = 'student_fail_exam';
   public const STUDENT_EXAM_COURSE = 'student_exam_course';
   public const STUDENT_EXAM_COURSE_FAILED = 'student_exam_course_failed';
   public const STUDENT_EXAM_COURSE_PASSED = 'student_exam_course_passed';

   //teacher performance
   public const TEACHER_EXAM_COURSE_PASSED = "teacher_exam_course_passed"; //teacher exam courses passed count
   public const TEACHER_EXAM_COURSE_FAILED = "teacher_exam_course_failed"; //teacher exam courses failed count
   public const TEACHER_EXAM_COURSE_CANDIDATE = "teacher_exam_course_candidate"; //teacher exam course candidate
   public const TEACHER_EXAM_COURSE_GRADE_DISTRIBUTION = "teacher_exam_course_grade_distribution"; //teacher exam course grades distribution

   //exam performance
   public const EXAM_GPA = "exam_gpa";
   public const EXAM = "exam";
   public const EXAM_CANDIDATE = "exam_candidate";
   public const EXAM_RESIT = "exam_resit";
   public const EXAM_PASSED = "exam_passed";
   public const EXAM_FAILED = "exam_failed";
   public const EXAM_GPA_DISTRIBUTION = "exam_gpa_distribution";
   public const EXAM_SCORE_DISTRIBUTION = "exam_score_distribution";
   public const EXAM_GRADE_DISTRIBUTION = "exam_grade_distribution";

   //resit
   public const RESIT_EXAM = "resit_exam";
   public const RESIT_EXAM_CANDIDATE = "resit_exam_candidate";
   public const RESIT_EXAM_PASSED = "resit_exam_passed";
   public const RESIT_EXAM_FAILED = "resit_exam_failed";
   public const RESIT = "resit_count";

   //school stats
   public const SCHOOL_EXAM = "school_exam";
   public const SCHOOL_EXAM_TYPE = "school_exam_type";
   public const SCHOOL_GPA = "school_gpa";
   public const SCHOOL_EXAM_TYPE_GPA = "school_exam_type_gpa";
   public const SCHOOL_EXAM_CANDIDATE = "school_exam_candidate";
   public const SCHOOL_EXAM_TYPE_CANDIDATE = "school_exam_type_candidate";
   public const SCHOOL_EXAM_CANDIDATE_PASSED = "school_exam_candidate_pass";
   public const SCHOOL_EXAM_TYPE_CANDIDATE_PASSED = "school_exam_type_candidate_passed";
   public const SCHOOL_EXAM_CANDIDATE_FAILED = "school_exam_candidate_failed";
   public const SCHOOL_EXAM_TYPE_CANDIDATE_FAILED = "school_exam_type_candidate_failed";

}
