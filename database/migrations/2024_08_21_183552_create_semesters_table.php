<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->integer('count');
            $table->string('program_name');
            $table->timestamps();
        });

        Schema::create('school_semesters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('timetable_published')->default(false);
            $table->enum('status', ['expired', 'active', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batches');
            $table->string('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('school_academic_years');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('school_semesters')) {
            Schema::table('school_semesters', function (Blueprint $table) {
                $table->dropForeign(['semester_id']);
                $table->dropForeign(['specialty_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['student_batch_id']);
                $table->dropForeign(['school_year_id']);
            });
        }

        Schema::dropIfExists('school_semesters');
        Schema::dropIfExists('semesters');
    }
};
