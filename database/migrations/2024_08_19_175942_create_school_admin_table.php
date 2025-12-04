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
        Schema::create('school_admins', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->index();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->string('role')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('cultural_background')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('city')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_admins');
    }
};

