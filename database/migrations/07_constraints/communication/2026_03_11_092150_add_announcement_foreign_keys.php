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
        Schema::table('announcement_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category_id')->nullable()->index();
            $table->foreign('category_id')->references('id')->on('announcement_categories')->onDelete('set null');
            $table->string('label_id')->index();
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcement_audiences', function (Blueprint $table) {
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('annoucement_authors', function (Blueprint $table) {
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcement_engagement_stats', function (Blueprint $table) {
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('student_announcements', function (Blueprint $table) {
            $table->string('student_id')->index();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('teacher_announcements', function (Blueprint $table) {
            $table->string('teacher_id')->index();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_admin_announcements', function (Blueprint $table) {
            $table->string('school_admin_id')->index();
            $table->foreign('school_admin_id')->references('id')->on('school_admins')->onDelete('cascade');
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_admin_announcements')) {
            Schema::table('school_admin_announcements', function (Blueprint $table) {
                $table->dropForeign(['school_admin_id']);
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('teacher_announcements')) {
            Schema::table('teacher_announcements', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('student_announcements')) {
            Schema::table('student_announcements', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcement_engagement_stats')) {
            Schema::table('announcement_engagement_stats', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('annoucement_authors')) {
            Schema::table('annoucement_authors', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcement_audiences')) {
            Schema::table('announcement_audiences', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropForeign(['label_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcement_categories')) {
            Schema::table('announcement_categories', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
