<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('timetable_published')->default(false);
            $table->enum('status', ['expired', 'active', 'pending'])->default('pending');
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('school_semesters');
        Schema::dropIfExists('semesters');
    }
};
