<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a resit exam's results are released to students, like
 * exam_result_publications for exams: one row per resit exam, published
 * now or scheduled for a later time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resit_result_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_branch_id', 64)->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->uuid('resit_exam_id')->unique();
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->uuid('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resit_result_publications');
    }
};
