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
        Schema::create('transfer_request', function (Blueprint $table) {
            $table->string('id');
            $table->string('current_school_name');
            $table->string('target_school_name');
            $table->string('specialty_name');
            $table->string('level_name');
            $table->string('department_name');
            $table->string('student_name');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_request');
    }
};
