<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('devicesable_id');
            $table->string('devicesable_type');
            $table->index(['devicesable_type', 'devicesable_id']);
            $table->string('device_token')->unique();
            $table->string('platform')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_devices')) {
            Schema::table('user_devices', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('user_devices');
    }
};
