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
        Schema::create('teacher_availability_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('status', ['added', 'not added'])->default('not added');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_availability_slots');
        Schema::dropIfExists('teacher_availabilities');
    }
};
