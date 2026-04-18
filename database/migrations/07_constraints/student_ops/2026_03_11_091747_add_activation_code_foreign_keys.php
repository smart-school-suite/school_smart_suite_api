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

        Schema::table('activation_codes', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('activation_code_type_id');
            $table->foreign('activation_code_type_id')->references('id')->on('activation_code_types');
        });

        Schema::table('activation_code_usages', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('activation_code_id');
            $table->foreign('activation_code_id')->references('id')->on('activation_codes');
        });

        Schema::table('activation_code_types', function (Blueprint $table) {
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activation_code_usages')) {
            Schema::table('activation_code_usages', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['activation_code_id']);
            });
        }

        if (Schema::hasTable('activation_codes')) {
            Schema::table('activation_codes', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['activation_code_type_id']);
            });
        }

        if (Schema::hasTable('activation_code_types')) {
            Schema::table('activation_code_types', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }
    }
};
