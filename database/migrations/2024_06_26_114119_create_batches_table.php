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
        Schema::create('batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('color');
            $table->json('mobile_icon')->nullable();
            $table->json('desktop_icon')->nullable();
            $table->timestamps();
        });

        Schema::create('user_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('assignable_id');
            $table->string('assignable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('user_batches');
    }
};
