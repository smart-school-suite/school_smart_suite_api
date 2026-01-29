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
        Schema::create('badge_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 100)->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('badge_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 100);
            $table->string('badge_category_id');
            $table->foreign('badge_category_id')->references('id')->on('badge_categories');
            $table->text('description')->nullable();
            $table->char('color', 7);
            $table->string('icon_code', 100);
            $table->timestamps();
        });


        Schema::create('user_badges', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('actorable_type');
            $table->string('actorable_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_types');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badge_categories');
    }
};
