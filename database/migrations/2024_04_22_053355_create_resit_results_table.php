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
        Schema::create('resit_results', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('former_ca_gpa', 4, 2);
            $table->decimal('new_ca_gpa', 4, 2);
            $table->decimal('former_exam_gpa', 4, 2);
            $table->decimal('new_exam_gpa', 4, 2);
            $table->json('score_details');
            $table->enum('exam_status', ['passed', 'failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_results');
    }
};
