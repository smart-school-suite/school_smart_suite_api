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
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('break')->default(false);
            $table->timestamps();
        });

        Schema::create('timetable_drafts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->unsignedInteger('draft_count')->default(1);
            $table->timestamps();
        });

        Schema::create('timetable_versions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('version_number');
            $table->string('label');
            $table->enum('scheduler_status', ['optimal', 'partial', 'failed', 'in_progress'])->default('in_progress');
            $table->timestamps();
        });

        Schema::create('active_timetables', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('timetable_prompts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('user_prompt');
            $table->json('scheduler_input')->nullable();
            $table->json('scheduler_output')->nullable();
            $table->json('ai_output')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('timetable_versions');
        Schema::dropIfExists('timetable_drafts');
        Schema::dropIfExists('timetable_prompts');
        Schema::dropIfExists('active_timetables');
    }
};
