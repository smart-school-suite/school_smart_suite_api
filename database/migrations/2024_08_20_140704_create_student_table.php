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
            $table->date('DOB')->nullable();
            $table->string('gender')->nullable();
            $table->string('fee_status')->default('owing');
            $table->decimal('total_fee_debt', 8, 2);
            $table->string('phone_one')->unique();
            $table->string('phone_two')->unique()->nullable();
            $table->string('religion')->nullable();
            $table->string('email');
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->boolean('deactivate')->default(false);
            $table->date('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
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
