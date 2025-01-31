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
        Schema::create('examtimetable', function (Blueprint $table) {
            $table->string('id');
            $table->date('day');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('duration');
            $table->string("school_year");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examtimetable');
    }
};
