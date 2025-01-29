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
        Schema::create('exam_type', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_name');
            $table->string('semester'); // first , second, third, fourth, fifth
            $table->enum('type', ['exam', 'ca', 'resit']); // ex
            $table->string('program_name'); //EX, CA, RESS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_type');
    }
};
