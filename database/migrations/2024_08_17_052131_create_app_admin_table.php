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
        Schema::create('app_admin', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('profile_picture')->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_admin');
    }
};
