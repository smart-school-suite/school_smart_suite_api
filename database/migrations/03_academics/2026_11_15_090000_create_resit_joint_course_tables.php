<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Joint resits, like joint exams (exam_jcs): a course students of several
 * specialties resit in the same academic year and resit exam type is sat
 * once, at one time set on the resit joint course timetable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resit_jcs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_branch_id', 64);
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('school_year_id', 64);
            $table->foreign('school_year_id')->references('id')->on('system_academic_years');
            $table->string('exam_type_id', 64);
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
            $table->timestamps();
            $table->unique(['school_branch_id', 'school_year_id', 'exam_type_id', 'course_id'], 'resit_jcs_term_course_unique');
        });

        Schema::create('resit_jc_refs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams')->cascadeOnDelete();
            $table->uuid('resit_jc_id');
            $table->foreign('resit_jc_id')->references('id')->on('resit_jcs')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['resit_exam_id', 'resit_jc_id'], 'resit_jc_refs_unique');
        });

        Schema::create('resit_jc_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('resit_jc_id')->index();
            $table->foreign('resit_jc_id')->references('id')->on('resit_jcs')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resit_jc_slots');
        Schema::dropIfExists('resit_jc_refs');
        Schema::dropIfExists('resit_jcs');
    }
};
