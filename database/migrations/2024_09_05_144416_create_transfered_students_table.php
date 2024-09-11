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
        Schema::create('transfered_students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('student_name');
            $table->string('from');
            $table->string('to');
            $table->boolean('status');
            $table->string('level');
            $table->string('specialty');
            $table->string('department');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfered_students');
    }
};
