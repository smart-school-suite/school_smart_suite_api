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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('school_course_types', function (Blueprint $table) {
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('course_type_id');
            $table->foreign('course_type_id')->references('id')->on('course_types');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('course_specialties', function (Blueprint $table) {
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('semester_joint_courses', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('school_academic_years')->onDelete('cascade');
            $table->unique(['school_branch_id', 'school_year_id', 'semester_id', 'course_id']);
        });

        Schema::table('joint_course_slots', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('hall_id');
            $table->foreign('hall_id')->references('id')->on('halls')->onDelete('cascade');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('semester_joint_course_id');
            $table->foreign('semester_joint_course_id')->references('id')->on('semester_joint_courses')->onDelete('cascade');
        });

        Schema::table('semester_joint_course_refs', function (Blueprint $table) {
            $table->string('semester_joint_course_id');
            $table->foreign('semester_joint_course_id')->references('id')->on('semester_joint_courses')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters')->onDelete('cascade');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->unique(['school_branch_id', 'school_semester_id', 'semester_joint_course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('semester_joint_course_refs')) {
            Schema::table('semester_joint_course_refs', function (Blueprint $table) {
                $table->dropForeign(['semester_joint_course_id']);
                $table->dropForeign(['school_semester_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('joint_course_slots')) {
            Schema::table('joint_course_slots', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['hall_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['semester_joint_course_id']);
            });
        }

        if (Schema::hasTable('semester_joint_courses')) {
            Schema::table('semester_joint_courses', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['semester_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['school_year_id']);
            });
        }

        if (Schema::hasTable('course_specialties')) {
            Schema::table('course_specialties', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('school_course_types')) {
            Schema::table('school_course_types', function (Blueprint $table) {
                $table->dropForeign(['course_id']);
                $table->dropForeign(['course_type_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['department_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['semester_id']);
            });
        }
    }
};
