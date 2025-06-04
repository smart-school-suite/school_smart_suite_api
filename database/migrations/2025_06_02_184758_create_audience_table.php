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
        Schema::create('preset_audiences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('target'); // e.g., 'students', 'teachers', 'parents'
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
        Schema::create('school_set_audience_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('audiences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('audienceable_id');
            $table->string('audienceable_type');
            $table->string('school_set_audience_group_id');
            $table->foreign('school_set_audience_group_id')->references('id')->on('school_set_audience_groups');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preset_audience');
        Schema::dropIfExists('school_set_audience_group');
        Schema::dropIfExists('audience');
    }
};
