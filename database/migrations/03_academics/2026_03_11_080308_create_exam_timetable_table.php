<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_timetable_draft', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->unsignedSmallInteger('draft_number');
            $table->timestamps();
        });

        Schema::create('exam_timetable_versions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedSmallInteger('version_number');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('exam_timetable_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('active_exam_timetable', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('exam_invigilators', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('actorable_id');
            $table->string('actorable_type');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('exam_invigilators');
        Schema::dropIfExists('active_exam_timetable');
        Schema::dropIfExists('exam_timetable_slots');
        Schema::dropIfExists('exam_timetable_versions');
        Schema::dropIfExists('exam_timetable_draft');
    }
};
