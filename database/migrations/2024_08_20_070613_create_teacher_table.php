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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('teacher_course_preferences');
        Schema::dropIfExists('teacher_availability_slots');
        Schema::dropIfExists('teacher_availabilities');
        Schema::dropIfExists('teacher_specialty_preferences');
    }
};
