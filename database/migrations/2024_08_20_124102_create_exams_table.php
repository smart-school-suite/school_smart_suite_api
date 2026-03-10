<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('weighted_mark', 6, 2)->nullable();
            $table->boolean('timetable_published')->default(false);
            $table->boolean('result_released')->default(false);
            $table->enum('status', ['finished', 'inprogress', 'pending'])->default('pending');
            $table->integer('expected_candidate_number')->default(0);
            $table->integer('evaluated_candidate_number')->default(0);
            $table->boolean('grading_added')->default(false);
            $table->timestamps();
        });

        Schema::create('exam_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_name');
            $table->string('semester');
            $table->enum('type', ['exam', 'ca', 'resit']);
            $table->string('program_name');
            $table->text('description')->nullable();
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
            $table->decimal('grade_points', 5, 2);
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

        Schema::table('exams', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_type_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_types');
            $table->string('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('school_academic_years')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('grades_category_id')->nullable();
            $table->foreign('grades_category_id')->references('id')->on('grade_scale_categories');
        });

        Schema::table('exam_types', function (Blueprint $table) {
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('exam_candidates', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('student_id')->nullable()->index();
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
        });

        Schema::table('marks', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
        });

        Schema::table('student_results', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('levels');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('student_results')) {
            Schema::table('student_results', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('marks')) {
            Schema::table('marks', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['courses_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
            });
        }

        if (Schema::hasTable('exam_candidates')) {
            Schema::table('exam_candidates', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['exam_id']);
                $table->dropForeign(['student_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['level_id']);
            });
        }

        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['exam_type_id']);
                $table->dropForeign(['school_year_id']);
                $table->dropForeign(['level_id']);
                $table->dropForeign(['semester_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['grades_category_id']);
            });
        }

        if (Schema::hasTable('exam_types')) {
            Schema::table('exam_types', function (Blueprint $table) {
                $table->dropForeign(['semester_id']);
            });
        }

        Schema::dropIfExists('student_results');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('exam_candidates');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
    }
};
