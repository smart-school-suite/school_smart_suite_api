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
        Schema::create('accessed_resit_student', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->boolean('grades_submitted')->default(false);
            $table->enum('student_accessed', ['pending', 'accessed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessed_resit_student');
    }
};
