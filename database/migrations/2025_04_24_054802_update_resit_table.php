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
        //
        Schema::table('resit_candidates', function(Blueprint $table){
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
        });

        Schema::table('resit_exams', function(Blueprint $table) {
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('resit_examtimetable', function(Blueprint $table) {
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
        });

        Schema::table('resit_results', function(Blueprint $table){
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
