<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exam timetables no longer keep a separate "active timetable" record, the
 * same as semester timetables: the version whose sessions are in
 * exam_timetable_slots is the active one. New databases never create the
 * table; this drops it from databases that already have it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('active_exam_timetables');
    }

    public function down(): void
    {
        if (Schema::hasTable('active_exam_timetables')) {
            return;
        }

        Schema::create('active_exam_timetables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version_id');
            $table->foreign('version_id')->references('id')->on('exam_timetable_versions');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });
    }
};
