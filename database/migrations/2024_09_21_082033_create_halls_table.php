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
        Schema::create('halls', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('capacity');
            $table->enum("type", ["lab", "lecture"]);
            $table->boolean('is_exam_hall')->default(true);
            $table->enum('status', ['available', 'unavailable'])->default('available');
            $table->integer('num_assigned_specialties')->default(0);
            $table->enum('assignment_status', ['assigned', 'unassigned'])->default('unassigned');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('specialty_halls', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
        Schema::dropIfExists('specialty_halls');
    }
};
