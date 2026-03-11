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
        Schema::table('affiliate_commissions', function (Blueprint $table) {
            $table->string('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('school_transaction_id');
            $table->foreign('school_transaction_id')->references('id')->on('school_transactions');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('affiliates', function (Blueprint $table) {
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('affiliate_applications', function (Blueprint $table) {
            $table->string('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('affiliate_payouts', function (Blueprint $table) {
            $table->string('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('affiliate_payouts')) {
            Schema::table('affiliate_payouts', function (Blueprint $table) {
                $table->dropForeign(['affiliate_id']);
                $table->dropForeign(['country_id']);
            });
        }

        if (Schema::hasTable('affiliate_applications')) {
            Schema::table('affiliate_applications', function (Blueprint $table) {
                $table->dropForeign(['affiliate_id']);
                $table->dropForeign(['country_id']);
            });
        }

        if (Schema::hasTable('affiliate_commissions')) {
            Schema::table('affiliate_commissions', function (Blueprint $table) {
                $table->dropForeign(['affiliate_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['school_transaction_id']);
                $table->dropForeign(['country_id']);
            });
        }

        if (Schema::hasTable('affiliates')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }
    }
};
