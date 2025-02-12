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
        Schema::create('school_semesters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date("start_date");
            $table->date("end_date");
            $table->integer("school_year_start");
            $table->integer('school_year_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_semesters');
    }
};
