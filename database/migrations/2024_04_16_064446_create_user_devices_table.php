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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('devicesable_id');
            $table->string('devicesable_type');
            $table->index(['devicesable_type', 'devicesable_id']);
            $table->string('device_token')->unique();
            $table->string('platform')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
