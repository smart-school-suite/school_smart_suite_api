<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the exam timetable scheduler needs:
 * - exam_timetable_versions.scheduler_status, like timetable_versions for the semester timetable
 * - exam_timetable_slots.type, regular or joint course session
 * - exam_session_invigs.exam_session_hall_id and school_branch_id, so each invigilator is tied to the hall they cover
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_timetable_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_timetable_versions', 'scheduler_status')) {
                $table->enum('scheduler_status', ['pending', 'generating', 'optimal', 'partial', 'failed', 'error'])
                    ->default('pending');
            }
        });

        Schema::table('exam_timetable_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_timetable_slots', 'type')) {
                $table->enum('type', ['regular', 'joint'])->default('regular');
            }
        });

        Schema::table('exam_session_invigs', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_session_invigs', 'exam_session_hall_id')) {
                $table->string('exam_session_hall_id', 64)->nullable()->index();
                $table->foreign('exam_session_hall_id')->references('id')->on('exam_session_halls')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('exam_session_invigs', 'school_branch_id')) {
                $table->string('school_branch_id', 64)->nullable()->index();
                $table->foreign('school_branch_id')->references('id')->on('school_branches');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_invigs', function (Blueprint $table) {
            if (Schema::hasColumn('exam_session_invigs', 'exam_session_hall_id')) {
                $table->dropForeign(['exam_session_hall_id']);
                $table->dropColumn('exam_session_hall_id');
            }

            if (Schema::hasColumn('exam_session_invigs', 'school_branch_id')) {
                $table->dropForeign(['school_branch_id']);
                $table->dropColumn('school_branch_id');
            }
        });

        Schema::table('exam_timetable_slots', function (Blueprint $table) {
            if (Schema::hasColumn('exam_timetable_slots', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('exam_timetable_versions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_timetable_versions', 'scheduler_status')) {
                $table->dropColumn('scheduler_status');
            }
        });
    }
};
