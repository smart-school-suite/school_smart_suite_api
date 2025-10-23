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
        Schema::create('exams', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('weighted_mark', 6, 2)->nullable();
            $table->string('school_year');
            $table->boolean('timetable_published')->default(false);
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->integer('expected_candidate_number')->default(0);
            $table->integer('evaluated_candidate_number')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
