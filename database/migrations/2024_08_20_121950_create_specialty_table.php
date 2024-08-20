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
        Schema::create('specialty', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('specialty_name');
            $table->decimal('registration_fee', 8, 2);
            $table->decimal('school_fee', 8, 2);
            $table->string('level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialty');
    }
};
