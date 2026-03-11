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
        Schema::table('exam_timetable_draft', function (Blueprint $table) {
            $table->string('exam_id')->index();
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('exam_timetable_versions', function (Blueprint $table) {
            $table->string('exam_timetable_draft_id');
            $table->foreign('exam_timetable_draft_id')->references('id')->on('exam_timetable_draft');
            $table->string('parent_version_id');
            $table->foreign('parent_version_id')->references('id')->on('exam_timetable_versions');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('exam_timetable_slots', function (Blueprint $table) {
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
        });

        Schema::table('active_exam_timetable', function (Blueprint $table) {
            $table->string('exam_timetable_version_id');
            $table->foreign('exam_timetable_version_id')->references('id')->on('exam_timetable_versions');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('exam_invigilators', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('exam_invigilators')) {
            Schema::table('exam_invigilators', function (Blueprint $table) {
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('active_exam_timetable')) {
            Schema::table('active_exam_timetable', function (Blueprint $table) {
                $table->dropForeign(['exam_timetable_version_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('exam_timetable_slots')) {
            Schema::table('exam_timetable_slots', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
                $table->dropForeign(['exam_timetable_version_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('exam_timetable_versions')) {
            Schema::table('exam_timetable_versions', function (Blueprint $table) {
                $table->dropForeign(['exam_timetable_draft_id']);
                $table->dropForeign(['parent_version_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('exam_timetable_draft')) {
            Schema::table('exam_timetable_draft', function (Blueprint $table) {
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
