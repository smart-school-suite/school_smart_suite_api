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

        Schema::create('stat_types', function(Blueprint $table){
            $table->string('id')->primary();
            $table->string('name');
            $table->string('program_name');
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('school_operational_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('school_academic_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('school_financial_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_academic_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_financial_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('school_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('school_resit_exam_stats', function(Blueprint $table){
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_exam_stats', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('student_resit_exam_stats', function(Blueprint $table){
            $table->string('id')->primary();
            $table->decimal('decimal_value', 12, 2)->nullable();
            $table->json('json_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->string('school_year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
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
