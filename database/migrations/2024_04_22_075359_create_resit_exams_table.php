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
        Schema::create('resit_exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('weighted_mark', 6, 2)->nullable();
            $table->boolean('timetable_published')->default(false);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->boolean("grading_added")->default(false);
            $table->integer('expected_candidate_number')->default(0);
            $table->integer('evaluated_candidate_number')->default(0);
            $table->string('school_year')->nullable();
            $table->timestamps();
        });

        Schema::create('resit_exam_references', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('resit_candidates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->boolean('grades_submitted')->default(false);
            $table->boolean('student_accessed')->default(false);
            $table->timestamps();
        });

        Schema::create('resit_marks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('score', 5, 2);
            $table->enum('grade_status', ['passed', 'failed'])->nullable();
            $table->decimal('grade_points', 5, 2);
            $table->string('gratification');
            $table->string('grade');
            $table->timestamps();
        });

        Schema::create('resit_results', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('former_ca_gpa', 4, 2);
            $table->decimal('new_ca_gpa', 4, 2);
            $table->decimal('former_exam_gpa', 4, 2);
            $table->decimal('new_exam_gpa', 4, 2);
            $table->json('score_details');
            $table->enum('exam_status', ['passed', 'failed']);
            $table->timestamps();
        });

                Schema::create('resit_exam_timetable_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_exams');
        Schema::dropIfExists('resit_exam_references');
        Schema::dropIfExists('resit_candidates');
        Schema::dropIfExists('resit_marks');
        Schema::dropIfExists('resit_results');
        Schema::dropIfExists('resit_exam_timetable_slots');
    }
};
