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

        Schema::create('setting_categories', function(Blueprint $table) {
           $table->string('id')->primary();
           $table->string('title');
           $table->enum('status', ['active', 'inactive'])->default('active');
           $table->timestamps();
        });
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->enum('allowed_value', ['integer', 'decimal', 'boolean']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('school_branch_app_settings', function(Blueprint $table){
           $table->string('id')->primary();
           $table->boolean('boolean_value')->nullable();
           $table->decimal('decimal_value')->nullable();
           $table->integer('integer_value')->nullable();
           $table->date('date_value');
           $table->dateTime('date_time_value');
           $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
