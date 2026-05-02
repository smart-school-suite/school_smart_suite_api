<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_jc_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('exam_jcs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('exam_jc_refs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('exam_jc_session_halls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedMediumInteger('candidate_count');
            $table->timestamps();
        });

        Schema::create('exam_session_jc_invigs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_jc_slot');
        Schema::dropIfExists('exam_jc');
        Schema::dropIfExists('exam_jc_refs');
        Schema::dropIfExists('exam_jc_session_hall');
        Schema::dropIfExists('exam_session_jc_invigs');
    }
};
