<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_logos', function (Blueprint $table) {
            $table->uuid('school')->index();
            $table->foreign('school')->references('id')->on('schools');
        });

        Schema::table('student_avatars', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::table('school_admin_avatars', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('school_admin_id')->index();
            $table->foreign('school_admin_id')->references('id')->on('school_admins');
        });

        Schema::table('teacher_avatars', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('teacher_id')->index();
            $table->foreign('teacher_id')->references('id')->on('teachers');
        });

        Schema::table('school_event_pics', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('event_id');
            $table->foreign('event_id')->references('id')->on('school_events');
        });
    }

    public function down(): void
    {
        Schema::table('school_logos', function (Blueprint $table) {
            $table->dropForeign(['school']);
            $table->dropColumn('school');
        });

        Schema::table('student_avatars', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['school_branch_id', 'student_id']);
        });

        Schema::table('school_admin_avatars', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['school_admin_id']);
            $table->dropColumn(['school_branch_id', 'school_admin_id']);
        });

        Schema::table('teacher_avatars', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['school_branch_id', 'teacher_id']);
        });

        Schema::table('school_event_pics', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['event_id']);
            $table->dropColumn(['school_branch_id', 'event_id']);
        });
    }
};
