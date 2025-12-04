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
        Schema::create('teachers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('address')->nullable();
            $table->string('gender');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
