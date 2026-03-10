<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::table('user_badges', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_badges')) {
            Schema::table('user_badges', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('badge_types')) {
            Schema::table('badge_types', function (Blueprint $table) {
                $table->dropForeign(['badge_category_id']);
            });
        }

        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badge_types');
        Schema::dropIfExists('badge_categories');
    }
};
