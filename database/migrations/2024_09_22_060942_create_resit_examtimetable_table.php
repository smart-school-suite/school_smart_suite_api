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
        Schema::create('resit_examtimetable', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('day');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('duration');
            $table->string('school_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_examtimetable');
    }
};
