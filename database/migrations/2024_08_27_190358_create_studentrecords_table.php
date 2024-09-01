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
        Schema::create('studentrecords', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('academic_year');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('student_name');
            $table->decimal('GPA', 3, 2);
            $table->json('records');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studentrecords');
    }
};
