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
        Schema::create('resit_marks', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('score', 4, 2);
            $table->enum('grade_status', ['passed', 'failed'])->nullable();
            $table->string('gratification');
            $table->string('grade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_marks');
    }
};
