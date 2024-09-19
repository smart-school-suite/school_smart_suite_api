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
        Schema::create('resitable_courses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resitable_courses');
    }
};
