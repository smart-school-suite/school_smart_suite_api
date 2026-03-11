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
        Schema::table('resit_exams', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('grades_category_id')->nullable();
            $table->foreign('grades_category_id')->references('id')->on('grade_scale_categories');
        });

        Schema::table('resit_exam_references', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_type_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
        });

        Schema::table('resit_candidates', function (Blueprint $table) {
            $table->string('resit_exam_id')->index();
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('resit_marks', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
        });

        Schema::table('resit_results', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('failed_exam_id');
            $table->foreign('failed_exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('resit_exam_timetable_slots', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('resit_exam_timetable_slots')) {
            Schema::table('resit_exam_timetable_slots', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['resit_exam_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
            });
        }

        if (Schema::hasTable('resit_results')) {
            Schema::table('resit_results', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['resit_exam_id']);
                $table->dropForeign(['failed_exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('resit_marks')) {
            Schema::table('resit_marks', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['courses_id']);
                $table->dropForeign(['resit_exam_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
            });
        }

        if (Schema::hasTable('resit_candidates')) {
            Schema::table('resit_candidates', function (Blueprint $table) {
                $table->dropForeign(['resit_exam_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('resit_exam_references')) {
            Schema::table('resit_exam_references', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['exam_type_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['semester_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['resit_exam_id']);
            });
        }

        if (Schema::hasTable('resit_exams')) {
            Schema::table('resit_exams', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['grades_category_id']);
            });
        }
    }
};
