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
        Schema::create('teacher', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('password');
            $table->string('profile_pricture')->nullable();
            $table->string('phone_one');
            $table->string('phone_two')->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('employment_status'); // E.g., Full-time, Part-time, Contractor
            $table->date('hire_date')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('last_performance_review')->nullable();
            $table->string('highest_qualification'); // e.g., Bachelor's, Master's
            $table->string('field_of_study'); // e.g., Computer Science, Business Administration
            $table->date('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('leave')->default(false);
            $table->boolean('holiday')->default(false);
            $table->string('city')->nullable();
            $table->string('cultural_background')->nullable();
            $table->string('religion')->nullable();
            $table->integer('years_experience');
            $table->decimal('salary', 8, 2);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher');
    }
};
