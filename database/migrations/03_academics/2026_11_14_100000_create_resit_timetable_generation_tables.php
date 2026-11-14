<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generated resit timetables, following the exam timetable: versions,
 * invigilators per resit exam, and for the active version its sessions
 * (resit_exam_timetable_slots), their halls and each hall's invigilators.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resit_timetable_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedSmallInteger('version_number');
            $table->string('label', 150);
            $table->enum('scheduler_status', ['pending', 'generating', 'optimal', 'partial', 'failed', 'error'])->default('pending');
            $table->uuid('resit_exam_id')->index();
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams')->cascadeOnDelete();
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('resit_invigilators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams')->cascadeOnDelete();
            $table->string('actorable_id', 64);
            $table->string('actorable_type', 100);
            $table->timestamps();
            $table->unique(['resit_exam_id', 'actorable_type', 'actorable_id'], 'resit_invigilators_person_unique');
        });

        Schema::table('resit_exam_timetable_slots', function (Blueprint $table) {
            $table->uuid('version_id')->nullable()->index();
            $table->foreign('version_id')->references('id')->on('resit_timetable_versions')->nullOnDelete();
        });

        Schema::create('resit_session_halls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedMediumInteger('candidate_count');
            $table->uuid('resit_slot_id')->index();
            $table->foreign('resit_slot_id')->references('id')->on('resit_exam_timetable_slots')->cascadeOnDelete();
            $table->string('hall_id', 64)->index();
            $table->foreign('hall_id')->references('id')->on('halls');
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });

        Schema::create('resit_session_invigs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('resit_slot_id')->index();
            $table->foreign('resit_slot_id')->references('id')->on('resit_exam_timetable_slots')->cascadeOnDelete();
            $table->uuid('resit_session_hall_id')->nullable()->index();
            $table->foreign('resit_session_hall_id')->references('id')->on('resit_session_halls')->cascadeOnDelete();
            $table->uuid('invigilator_id')->index();
            $table->foreign('invigilator_id')->references('id')->on('resit_invigilators');
            $table->string('school_branch_id', 64)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resit_session_invigs');
        Schema::dropIfExists('resit_session_halls');

        Schema::table('resit_exam_timetable_slots', function (Blueprint $table) {
            $table->dropForeign(['version_id']);
            $table->dropColumn('version_id');
        });

        Schema::dropIfExists('resit_invigilators');
        Schema::dropIfExists('resit_timetable_versions');
    }
};
