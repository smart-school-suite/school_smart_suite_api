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
        Schema::create('constraint_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('program_name', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('constraint_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('program_name', 200);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('semester_timetable_constraints', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('program_name', 150);
            $table->char('code', 10);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('constraint_type_id');
            $table->foreign('constraint_type_id')->references('id')->on('constraint_types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constraint_types');
        Schema::dropIfExists('semester_timetable_constraints');
        Schema::dropIfExists('constraint_categories');
    }
};
