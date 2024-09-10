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
        Schema::create('transfer_request', function (Blueprint $table) {
            $table->string('id');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('current_school_id');
            $table->foreign('current_school_id')->references('id')->on('school_branches');
            $table->string('target_school_id');
            $table->foreign('target_school_id')->references('id')->on('school_branches');
            $table->string('current_school_name');
            $table->string('target_school_name');
            $table->string('specialty_name');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('level_name');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('department_name');
            $table->string('student_name');
            $table->string('status')->default('Pending');
            $table->string('parent_id');
            $table->foreign('parent_id')->references('id')->on('parents');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_request');
    }
};
