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
        Schema::table('user_badges', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('badge_types', function (Blueprint $table) {
            $table->string('badge_category_id');
            $table->foreign('badge_category_id')->references('id')->on('badge_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
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
    }
};
