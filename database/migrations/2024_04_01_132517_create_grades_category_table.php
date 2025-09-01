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
        Schema::create('grades_category', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->enum('status', ['active', 'inactive']);
            $table->enum('exam_type', ['exam', 'resit', 'ca']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades_category');
    }
};
