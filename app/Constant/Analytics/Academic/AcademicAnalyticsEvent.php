<?php

namespace App\Constant\Analytics\Academic;

class AcademicAnalyticsEvent
{
    public const EXAM_CANDIDATE_EVALUATED = "exam.candidate.evaluated";
    public const EXAM_CANDIDATE_COURSE_EVALUATED = "exam.candidate.course.evaluated";
    public const EXAM_CANDIDATE_COURSE_PASSED = "exam.candidate.course.passed";
    public const EXAM_CANDIDATE_COURSE_FAILED = "exam.candidate.course.failed";
    public const EXAM_CANDIDATE_RESIT_INCURRED = "exam.candidate.resit.incurred";
    public const EXAM_CANDIDATE_PASSED = "exam.candidate.passed";
    public const EXAM_CANDIDATE_FAILED = "exam.candidate.failed";
    public const EXAM_CANDIDATE_CREATED =  "exam.candidate.created";
    public const EXAM_CANDIDATE_COURSE_CREATED = "exam.candidate.course.created";
    public const EXAM_CREATED = "exam.created";

    public const STUDENT_EXAM_CREATED = "student.exam.created";
    public const RESIT_EXAM_CANDIDATE_EVALUATED = "resitExam.candidate.evaluated";
    public const RESIT_EXAM_CANDIDATE_PASSED = "resitExam.candidate.passed";
    public const RESIT_EXAM_CANDIDATE_FAILED = "resitExam.candidate.failed";
    public const RESIT_EXAM_CREATED = "resitExam.created";
    public const RESIT_EXAM_CANDIDATE_CREATED = "resitExam.candidate.created";

    public const TEACHER_EXAM_COURSE_CREATED = "teacher.exam.course.created";

}
