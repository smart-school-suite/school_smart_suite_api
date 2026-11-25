<?php

namespace App\Services\Import;

/**
 * Every spreadsheet import the API runs, with the names people see for it.
 *
 * The value is what system_jobs.type stores, so it must never change for an
 * existing case. Titles, modules and record names are read from here by the
 * jobs list, the progress events and the import jobs themselves, so a job is
 * never shown to anyone by its raw key (e.g. "teacher_specialty_import").
 */
enum ImportType: string
{
    case SCHOOL_ADMIN = 'school_admin_import';
    case TEACHER = 'teacher_import';
    case TEACHER_SPECIALTY = 'teacher_specialty_import';
    case TEACHER_COURSE = 'teacher_course_import';
    case DEPARTMENT = 'department_import';
    case SPECIALTY = 'specialty_import';
    case HALL = 'hall_import';
    case SPECIALTY_HALL = 'specialty_hall_import';
    case COURSE = 'course_import';
    case JOINT_COURSE = 'joint_course_import';
    case STUDENT = 'student_import';
    case PARENT = 'parent_import';
    case STUDENT_BATCH = 'student_batch_import';
    case GRADE_SCALE = 'grade_scale_import';

    /** Shown as the job's title, e.g. "Teacher Specialty Import". */
    public function title(): string
    {
        return "{$this->module()} Import";
    }

    /** The area of the app the job belongs to. */
    public function module(): string
    {
        return match ($this) {
            self::SCHOOL_ADMIN => 'School Admin',
            self::TEACHER => 'Teacher',
            self::TEACHER_SPECIALTY => 'Teacher Specialty',
            self::TEACHER_COURSE => 'Teacher Course',
            self::DEPARTMENT => 'Department',
            self::SPECIALTY => 'Specialty',
            self::HALL => 'Hall',
            self::SPECIALTY_HALL => 'Specialty Hall',
            self::COURSE => 'Course',
            self::JOINT_COURSE => 'Joint Course',
            self::STUDENT => 'Student',
            self::PARENT => 'Guardian',
            self::STUDENT_BATCH => 'Student Batch',
            self::GRADE_SCALE => 'Grade Scale',
        };
    }

    /**
     * The system_job_categories name the job is filed under. The jobs popover
     * of each page filters on it, so it must match JobCategorySeeder.
     */
    public function category(): string
    {
        return match ($this) {
            self::SCHOOL_ADMIN => 'School Admin',
            self::TEACHER, self::TEACHER_SPECIALTY, self::TEACHER_COURSE => 'Teacher',
            self::DEPARTMENT => 'Department',
            self::SPECIALTY => 'Specialty',
            self::HALL, self::SPECIALTY_HALL => 'Hall',
            self::COURSE, self::JOINT_COURSE => 'Course',
            self::STUDENT => 'Student',
            self::PARENT => 'Parent',
            self::STUDENT_BATCH => 'Student Batch',
            self::GRADE_SCALE => 'Grade Scale',
        };
    }

    /** What one row creates, plural and lower case: "imported 12 teacher course assignments". */
    public function recordsLabel(): string
    {
        return match ($this) {
            self::SCHOOL_ADMIN => 'school admins',
            self::TEACHER => 'teachers',
            self::TEACHER_SPECIALTY => 'teacher specialty assignments',
            self::TEACHER_COURSE => 'teacher course assignments',
            self::DEPARTMENT => 'departments',
            self::SPECIALTY => 'specialties',
            self::HALL => 'halls',
            self::SPECIALTY_HALL => 'specialty hall assignments',
            self::COURSE => 'courses',
            self::JOINT_COURSE => 'joint courses',
            self::STUDENT => 'students',
            self::PARENT => 'guardians',
            self::STUDENT_BATCH => 'student batches',
            self::GRADE_SCALE => 'grades',
        };
    }

    /** Folder on the r2 disk the uploaded file waits in until the job reads it. */
    public function storageFolder(string $schoolBranchId): string
    {
        $folder = str_replace('_import', '', $this->value);

        return "imports/{$folder}/{$schoolBranchId}";
    }
}
