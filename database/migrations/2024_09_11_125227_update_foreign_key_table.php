<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('schools', function (Blueprint $table) {
            $table->string('country_id')->after('id');
            $table->foreign('country_id')->references('id')->on('country');
        });

        Schema::table('school_admin', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('teacher', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('department', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('specialty', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });

        Schema::table('marks', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
        });

        Schema::table('exams', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_type_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_type');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('fee_payment', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student')->onDelete('cascade');
        });

        Schema::table('parents', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('school_branches', function(Blueprint $table){
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('courses', function(Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('student', function(Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('parent_id');
            $table->foreign('parent_id')->references('id')->on('parents');
        });

        Schema::table('events', function(Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('timetables', function(Blueprint $table){
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels')->onDelete('cascade');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('instructor_availabilities', function(Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels')->onDelete('cascade');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
        });

        Schema::table('grades', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('letter_grade_id');
            $table->foreign('letter_grade_id')->references('id')->on('letter_grade');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
        });

        Schema::table('examtimetable', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
        });

        Schema::table('report_card', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });

        Schema::table('transfered_students', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('transfer_request', function (Blueprint $table){
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('current_school_id');
            $table->foreign('current_school_id')->references('id')->on('school_branches');
            $table->string('target_school_id');
            $table->foreign('target_school_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('parent_id');
            $table->foreign('parent_id')->references('id')->on('parents');
        });

        Schema::table('exam_type', function (Blueprint $table){
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });
       
        Schema::table('subcriptions', function (Blueprint $table){
            $table->string('description_id');
            $table->foreign('description_id')->references('id')->on('subcription_features');
        });
       
        Schema::table('school_expenses', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('expenses_category_id');
            $table->foreign('expenses_category_id')->references('id')->on('school_expenses_category');
        });

        Schema::table('school_expenses_category', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_batch', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('archievements', function (Blueprint $table){
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
