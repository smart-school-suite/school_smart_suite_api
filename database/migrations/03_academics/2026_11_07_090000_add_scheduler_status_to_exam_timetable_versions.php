<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_timetable_versions', function (Blueprint $table) {
            $table->enum('scheduler_status', ['pending', 'generating', 'optimal', 'partial', 'failed', 'error'])
                ->default('pending')
                ->after('label');
        });

        // Invigilators are assigned per hall: a session split over several halls
        // has its own invigilators in each one.
        Schema::table('exam_session_invigs', function (Blueprint $table) {
            $table->string('exam_session_hall_id', 64)->nullable()->after('exam_slot_id');
            $table->foreign('exam_session_hall_id')->references('id')->on('exam_session_halls')->onDelete('cascade');
            $table->string('school_branch_id', 64)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_invigs', function (Blueprint $table) {
            $table->dropForeign(['exam_session_hall_id']);
            $table->dropColumn(['exam_session_hall_id', 'school_branch_id']);
        });

        Schema::table('exam_timetable_versions', function (Blueprint $table) {
            $table->dropColumn('scheduler_status');
        });
    }
};
