<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 200);
            $table->char('phone', 12);
            $table->string('promo_code', 100)->nullable()->unique();
            $table->decimal('commission_percentage', 6, 2)->nullable();
            $table->decimal('discount_percentage', 6, 2)->nullable();
            $table->decimal('account_balance', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'disabled'])->default('inactive');
            $table->timestamps();
        });

        Schema::create('affiliate_applications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('percentage', 6, 2);
            $table->decimal('amount', 8, 2);
            $table->timestamps();
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['rejected', 'pending', 'approved']);
            $table->string('payment_method');
            $table->string('payment_ref');
            $table->timestamps();
        });

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

        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_applications');
        Schema::dropIfExists('affiliates');
    }
};
