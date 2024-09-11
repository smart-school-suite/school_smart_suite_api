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
        Schema::create('student', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('DOB');
            $table->string('gender');
            $table->string('fee_status')->default('owing');
            $table->decimal('total_fee_debt', 8, 2);
            $table->string('phone_one')->nullable();
            $table->string('phone_two')->nullable();
            $table->string('religion')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};
