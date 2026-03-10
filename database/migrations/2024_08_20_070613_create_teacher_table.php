<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('address')->nullable();
            $table->integer('num_assigned_courses')->default(0);
            $table->enum('course_assignment_status', ['assigned', 'unassigned'])->default('unassigned');
            $table->integer('num_assigned_specialties')->default(0);
            $table->enum('specialty_assignment_status', ['assigned', 'unassigned'])->default('unassigned');
            $table->enum('sub_status', ['subscribed', 'expired', 'renewed', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::create('teacher_course_preferences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('teacher_availability_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('status', ['added', 'not added'])->default('not added');
            $table->timestamps();
        });

        Schema::create('teacher_specialty_preferences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('gender_id');
            $table->foreign('gender_id')->references('id')->on('genders');
        });

        Schema::table('teacher_course_preferences', function (Blueprint $table) {
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->string('course_id')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('teacher_availability_slots', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('teacher_availability_id');
            $table->foreign('teacher_availability_id')->references('id')->on('teacher_availabilities');
        });

        Schema::table('teacher_availabilities', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
        });

        Schema::table('teacher_specialty_preferences', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teachers');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_availability_slots')) {
            Schema::table('teacher_availability_slots', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['school_semester_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['teacher_availability_id']);
            });
        }

        if (Schema::hasTable('teacher_availabilities')) {
            Schema::table('teacher_availabilities', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['school_semester_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['specialty_id']);
            });
        }

        if (Schema::hasTable('teacher_course_preferences')) {
            Schema::table('teacher_course_preferences', function (Blueprint $table) {
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('teacher_specialty_preferences')) {
            Schema::table('teacher_specialty_preferences', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['teacher_id']);
                $table->dropForeign(['specialty_id']);
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['gender_id']);
            });
        }

        Schema::dropIfExists('teacher_availability_slots');
        Schema::dropIfExists('teacher_availabilities');
        Schema::dropIfExists('teacher_course_preferences');
        Schema::dropIfExists('teacher_specialty_preferences');
        Schema::dropIfExists('teachers');
    }
};
