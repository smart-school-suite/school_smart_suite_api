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
        Schema::create('exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('weighted_mark', 6, 2)->nullable();
            $table->string('school_year');
            $table->boolean('timetable_published')->default(false);
            $table->boolean('result_released')->default(false);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->integer('expected_candidate_number')->default(0);
            $table->integer('evaluated_candidate_number')->default(0);
            $table->boolean("grading_added")->default(false);
            $table->timestamps();
        });

        Schema::create('exam_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_name');
            $table->string('semester'); // first , second, third, fourth, fifth
            $table->enum('type', ['exam', 'ca', 'resit']); // ex
            $table->string('program_name'); //EX, CA, RESS
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_timetable_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('duration');
            $table->timestamps();
        });

        Schema::create('exam_candidates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->boolean('grades_submitted')->default(false);
            $table->enum('student_accessed', ['pending', 'accessed'])->default('pending');
            $table->timestamps();
        });

                Schema::create('marks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('score', 5, 2);
            $table->decimal('grade_points', 5,2);
            $table->enum('grade_status', ['passed', 'failed'])->default('passed');
            $table->string('gratification');
            $table->enum('resit_status', ['resit', 'no_resit', 'high_resit_potential', 'low_resit_potential']);
            $table->string('grade');
            $table->timestamps();
        });

                Schema::create('student_results', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('gpa', 4, 2);
            $table->decimal('total_score', 6, 2);
            $table->enum('exam_status', ['passed', 'failed']);
            $table->json('score_details');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('exam_timetable_slots');
        Schema::dropIfExists('exam_candidates');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('student_results');
    }
};
