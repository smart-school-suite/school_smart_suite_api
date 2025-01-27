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
        Schema::create('parents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('address');
            $table->string('password');
            $table->string("email")->unique();
            $table->string('profile_picture')->nullable();
            $table->string('language_preference');
            $table->string('phone_one');
            $table->string('phone_two')->nullable();
            $table->string('occupation')->nullable();
            $table->string('relationship_to_student')->nullable();
            $table->string('preferred_contact_method')->default("All");
            $table->boolean('receive_notifications')->default(true);
            $table->string('marital_status')->nullable();
            $table->string('preferred_language_of_communication')->nullable();
            $table->boolean('is_active_in_pta')->default(false);
            $table->string('cultural_background')->nullable();
            $table->string('religion')->nullable();
            $table->string('referral_source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
