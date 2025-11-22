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
        Schema::create('school_branch_api_keys', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('api_key')->unique();
            $table->integer('current_num_school_admins')->default(1);
            $table->integer('current_num_students')->default(0);
            $table->integer('current_num_parents')->default(0);
            $table->integer('current_number_teacher')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_branch_api_keys');
    }
};
