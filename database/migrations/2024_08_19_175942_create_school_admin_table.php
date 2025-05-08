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
        Schema::create('school_admin', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->string('role')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('employment_status')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('last_performance_review')->nullable();
            $table->string('work_location')->nullable();
            $table->string('highest_qualification')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('leave')->default(false);
            $table->boolean('holiday')->default(false);
            $table->string('cultural_background')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('religion')->nullable();
            $table->integer('years_experience')->nullable();
            $table->decimal('salary', 8, 2)->nullable();
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
        Schema::dropIfExists('school_admin');
    }
};

