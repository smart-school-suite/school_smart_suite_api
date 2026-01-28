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
        Schema::create('system_school_years', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->unsignedBigInteger('year_start');
            $table->unsignedBigInteger('year_end');
            $table->timestamps();
        });

        Schema::table('school_academic_years', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('school_year_id');
            $table->foreign('school_year_id')->references('id')->on('system_school_years')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_academic_years');
        Schema::dropIfExists('system_school_years');
    }
};
