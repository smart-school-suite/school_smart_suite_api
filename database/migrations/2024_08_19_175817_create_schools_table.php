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
        Schema::create('schools', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->enum('type', ['private', 'government']);
            $table->date('established_year')->nullable();
            $table->string('school_logo')->nullable();
            $table->text('motor')->nullable();
            $table->timestamps();
        });

        Schema::create('school_branches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 100);
            $table->string('abbreviation', 10);
            $table->string('address', 100)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone_one', 20)->nullable();
            $table->string('phone_two', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->integer('semester_count')->default(2);
            $table->integer('final_semester')->default(2);
            $table->decimal('max_gpa', 5, 2)->default(4.00);
            $table->decimal('resit_fee', 8, 2)->default(3000.00);
            $table->timestamps();
        });

        Schema::create('school_branch_api_keys', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('api_key')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
        Schema::dropIfExists('school_branches');
        Schema::dropIfExists('school_branch_api_keys');
    }
};
