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
        Schema::create('resit_candidates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->boolean('grades_submitted')->default(false);
            $table->boolean('student_accessed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_candidates');
    }
};
