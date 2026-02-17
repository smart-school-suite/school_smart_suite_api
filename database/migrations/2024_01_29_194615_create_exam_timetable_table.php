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

        Schema::create('exam_timetable_draft', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->unsignedSmallInteger('draft_number');
            $table->string('exam_id')->index();
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('exam_timetable_versions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedSmallInteger('version_number');
            $table->string('name');
            $table->string('exam_timetable_draft_id');
            $table->foreign('exam_timetable_draft_id')->references('id')->on('exam_timetable_draft');
            $table->string('parent_version_id');
            $table->foreign('parent_version_id')->references('id')->on('exam_timetable_versions');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('exam_timetable_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('exam_timetable_version_id');
            $table->foreign('exam_timetable_version_id')->references('id')->on('exam_timetable_versions');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('active_exam_timetable', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_timetable_version_id');
            $table->foreign('exam_timetable_version_id')->references('id')->on('exam_timetable_versions');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('exam_invigilators', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('actorable_id');
            $table->string('actorable_type');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_timetable_draft');
        Schema::dropIfExists('exam_timetable_versions');
        Schema::dropIfExists('exam_timetable_slots');
        Schema::dropIfExists('active_exam_timetable');
        Schema::dropIfExists('exam_invigilators');
    }
};
