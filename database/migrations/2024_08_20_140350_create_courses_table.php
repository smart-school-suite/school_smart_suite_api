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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('course_code');
            $table->string('course_title');
            $table->integer('credit');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text("description")->nullable();
            $table->timestamps();
        });

        Schema::create('course_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 100);
            $table->char('text_color', 8)->nullable();
            $table->char('background_color', 8)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('school_course_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('course_types');
        Schema::dropIfExists('school_course_types');
    }
};
