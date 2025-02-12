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
        Schema::create('grades', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('grade_points');
            $table->enum('grade_status', ['resit', 'passed', 'failed', 'potential resit'])->default('resit');
            $table->decimal('minimum_score', 3, 1);
            $table->decimal('maximum_score', 3, 2);
            $table->string("determinant");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
