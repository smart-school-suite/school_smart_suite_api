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
        Schema::create('student_dropout_data', function(Blueprint $table) {
           $table->string('id')->primary();
           $table->json('student_resit_data')->nullable();
           $table->json('student_marks_data')->nullable();
           $table->json('student_results_data')->nullable();
           $table->json('student_registration_fee_data')->nullable();
           $table->json('student_additional_fee_data')->nullable();
           $table->json('student_tuition_fee_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_dropout_data');
    }
};
