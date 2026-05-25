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
        Schema::table('school_branch_api_keys', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->string('country_id')->after('id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('school_branches', function (Blueprint $table) {
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_branch_api_keys')) {
            Schema::table('school_branch_api_keys', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('school_branches')) {
            Schema::table('school_branches', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
            });
        }

        if (Schema::hasTable('schools')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }
    }
};
