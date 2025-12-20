<?php

namespace App\Constant\Analytics\Academic;

class AcademicAnalyticsDimension
{
    public const STUDENT = 'student';
    public const TEACHER = 'teacher';
    public const COURSE = 'course';
    public const DEPARTMENT = 'department';
    public const SPECIALTY = 'specialty';

    // Exam-specific
    public const EXAM = 'exam';
    public const EXAM_TYPE = 'exam_type'; // e.g first_semester, second_semester, resit

    public const TERM = 'term';
    public const ACADEMIC_YEAR = 'academic_year';
}
