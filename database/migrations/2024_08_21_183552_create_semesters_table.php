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
        Schema::create('semesters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->integer('count');
            $table->string('program_name');
            $table->timestamps();
        });

        Schema::create('school_semesters', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date("start_date");
            $table->date("end_date");
            $table->string("school_year");
            $table->boolean("timetable_published")->default(false);
            $table->enum('status', ['expired', 'active', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
         Schema::dropIfExists('school_semesters');
    }
};
