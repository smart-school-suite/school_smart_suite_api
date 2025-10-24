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


        Schema::create('setting_categories', function (Blueprint $table) {
             $table->string('id')->primary();
             $table->string('name')->unique();
             $table->string('description')->nullable();
             $table->timestamps();
        });

         Schema::create('setting_definations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->enum('data_type', ['string', 'integer', 'boolean', 'decimal', 'json']);
            $table->json('default_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('school_branch_settings', function (Blueprint $table ) {
             $table->string('id')->primary();
             $table->json('value')->nullable();
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_branch_settings');
        Schema::dropIfExists('setting_categories');
        Schema::dropIfExists('setting_definations');
    }
};
