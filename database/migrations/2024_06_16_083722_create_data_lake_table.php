<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('stat_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('program_name')->index();
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('school_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

         Schema::create('school_ca_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

         Schema::create('student_ca_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

        Schema::create('additional_fee_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

        Schema::create('resit_fee_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

        Schema::create('school_expenses_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('tuition_fee_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('election_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('election_winner_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('student_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('class_timetable_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_timetable_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('department_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->timestamps();
        });

        Schema::create('specialty_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('course_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_fee_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('additional_fee_trans_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('election_vote_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create("resit_fee_trans_stats", function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create("tuition_fee_trans_stats", function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value')->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
        });

        Schema::create('progressive_stats', function(Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();

       });

       Schema::create('election_application_stats', function(Blueprint $table) {
         $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
       });

       Schema::create('school_admin_stats', function(Blueprint $table){
            $table->string('id')->primary();
            $table->integer('integer_value')->nullable();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->timestamps();
       });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_categories');
        Schema::dropIfExists('stat_types');
        Schema::dropIfExists('school_operational_stats');
        Schema::dropIfExists('school_academic_stats');
        Schema::dropIfExists('school_financial_stats');
        Schema::dropIfExists('student_financial_stats');
        Schema::dropIfExists('school_exam_stats');
        Schema::dropIfExists('student_exam_stats');
    }
};
