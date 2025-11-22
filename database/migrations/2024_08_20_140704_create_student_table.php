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
        Schema::create('students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('DOB')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone_one')->unique()->nullable();
            $table->string('phone_two')->unique()->nullable();
            $table->string('email');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->boolean('deactivate')->default(false);
            $table->date('last_login_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('dropout_status')->default(false);
            $table->enum('payment_format', ["one time", "installmental"])->default('installmental');
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
