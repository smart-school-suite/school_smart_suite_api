<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('graduation_records', function (Blueprint $table) {
            $table->uuid('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('batch_id');
            $table->foreign('batch_id')->references('id')->on('graduation_batches');
            $table->uuid('promotion_run_id');
            $table->foreign('promotion_run_id')->references('id')->on('promotion_runs');
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('students');
        });

        Schema::table('graduation_batches', function (Blueprint $table) {
            $table->uuid('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('system_academic_years');
            $table->uuid('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        //
    }
};
