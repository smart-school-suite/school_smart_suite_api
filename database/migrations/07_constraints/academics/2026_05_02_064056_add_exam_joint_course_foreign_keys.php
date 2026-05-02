<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_jc_slots', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('exam_jc_id');
            $table->foreign('exam_jc_id')->references('id')->on('exam_jcs');
        });

        Schema::table('exam_jcs', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->uuid('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('system_academic_years');
            $table->uuid('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('exam_jc_refs', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->uuid('exam_js_id');
            $table->foreign('exam_js_id')->references('id')->on('exam_jcs');
        });

        Schema::table('exam_jc_session_halls', function (Blueprint $table) {
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('hall_id');
            $table->foreign('hall_id')->references('id')->on('halls');
            $table->uuid('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->uuid('exam_jc_slot_id');
            $table->foreign('exam_jc_slot_id')->references('id')->on('exam_jc_slots');
        });

        Schema::table('exam_session_jc_invigs', function (Blueprint $table) {
            $table->uuid('exam_jc_session_hall_id');
            $table->foreign('exam_jc_session_hall_id')->references('id')->on('exam_jc_session_halls');
            $table->uuid('invigilator_id');
            $table->foreign('invigilator_id')->references('id')->on('exam_invigs');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_jc_slots')) {
            Schema::table('exam_jc_slots', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['exam_jc_id']);
            });
        }

        if (Schema::hasTable('exam_jcs')) {
            Schema::table('exam_jcs', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['school_year_id']);
                $table->dropForeign(['semester_id']);
            });
        }

        if (Schema::hasTable('exam_jc_refs')) {
            Schema::table('exam_jc_refs', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['exam_js_id']);
            });
        }

        if (Schema::hasTable('exam_jc_session_hall')) {
            Schema::table('exam_jc_session_hall', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['hall_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['exam_jc_slot_id']);
            });
        }

        if (Schema::hasTable('exam_session_jc_invigs')) {
            Schema::table('exam_session_jc_invigs', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['invigilator_id']);
                $table->dropForeign(['exam_jc_session_hall_id']);
            });
        }
    }
};
