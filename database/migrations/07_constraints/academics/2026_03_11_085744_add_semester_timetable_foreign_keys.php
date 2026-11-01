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
        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->uuid('course_id')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->uuid('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->uuid('version_id');
            $table->foreign('version_id')->references('id')->on('timetable_versions')->onDelete('cascade');
            $table->uuid('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('hall_id')->nullable();
            $table->foreign('hall_id')->references('id')->on('halls');
        });

        Schema::table('timetable_versions', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->uuid('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (Schema::hasTable('timetable_versions')) {
            Schema::table('timetable_versions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('timetable_slots')) {
            Schema::table('timetable_slots', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['hall_id']);
            });
        }
    }
};
