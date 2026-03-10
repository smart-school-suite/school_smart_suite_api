<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('key')->unique();
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

        Schema::create('school_branch_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::table('school_branch_settings', function (Blueprint $table) {
            $table->string('setting_defination_id');
            $table->foreign('setting_defination_id')->references('id')->on('setting_definations');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('setting_definations', function (Blueprint $table) {
            $table->string('setting_category_id');
            $table->foreign('setting_category_id')->references('id')->on('setting_categories');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('school_branch_settings')) {
            Schema::table('school_branch_settings', function (Blueprint $table) {
                $table->dropForeign(['setting_defination_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('setting_definations')) {
            Schema::table('setting_definations', function (Blueprint $table) {
                $table->dropForeign(['setting_category_id']);
            });
        }

        Schema::dropIfExists('school_branch_settings');
        Schema::dropIfExists('setting_definations');
        Schema::dropIfExists('setting_categories');
    }
};
