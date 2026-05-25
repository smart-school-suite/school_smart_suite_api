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
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->string('course_id')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('version_id', 64);
            $table->foreign('version_id')->references('id')->on('timetable_versions');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('hall_id')->nullable();
            $table->foreign('hall_id')->references('id')->on('halls');
        });

        Schema::table('timetable_versions', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters')->onDelete('cascade');
        });

        Schema::table('active_timetables', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('timetable_version_id');
            $table->foreign('timetable_version_id')->references('id')->on('timetable_versions')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('active_timetables')) {
            Schema::table('active_timetables', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['timetable_version_id']);
                $table->dropForeign(['school_semester_id']);
            });
        }

        if (Schema::hasTable('timetable_versions')) {
            Schema::table('timetable_versions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('timetable_slots')) {
            Schema::table('timetable_slots', function (Blueprint $table) {
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['school_semester_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['hall_id']);
            });
        }
    }
};
