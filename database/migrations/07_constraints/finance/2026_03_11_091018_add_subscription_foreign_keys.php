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
        Schema::table('features', function (Blueprint $table) {
            $table->string('country_id')->index();
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('country_id')->index();
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('plan_features', function (Blueprint $table) {
            $table->string('feature_id');
            $table->foreign('feature_id')->references('id')->on('features');
            $table->string('plan_id');
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });

        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('plan_id');
            $table->foreign('plan_id')->references('id')->on('plans');
        });

        Schema::table('school_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
        });

        Schema::table('subscription_usage', function (Blueprint $table) {
            $table->string('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('school_subscriptions');
            $table->string('feature_plan_id');
            $table->foreign('feature_plan_id')->references('id')->on('plan_features');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('plan_recs', function (Blueprint $table) {
            $table->string('feature_id');
            $table->foreign('feature_id')->references('id')->on('features');
            $table->string('source_plan_id');
            $table->foreign('source_plan_id')->references('id')->on('plans');
            $table->string('target_plan_id');
            $table->foreign('target_plan_id')->references('id')->on('plans');
        });

        Schema::table('plan_rec_conds', function (Blueprint $table) {
            $table->string('plan_rec_id');
            $table->foreign('plan_rec_id')->references('id')->on('plan_recs');
        });

        Schema::table('plan_rec_copies', function (Blueprint $table) {
            $table->string('plan_rec_cond_id');
            $table->foreign('plan_rec_cond_id')->references('id')->on('plan_rec_conds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('plan_rec_copies')) {
            Schema::table('plan_rec_copies', function (Blueprint $table) {
                $table->dropForeign(['plan_rec_cond_id']);
            });
        }

        if (Schema::hasTable('plan_rec_conds')) {
            Schema::table('plan_rec_conds', function (Blueprint $table) {
                $table->dropForeign(['plan_rec_id']);
            });
        }

        if (Schema::hasTable('plan_recs')) {
            Schema::table('plan_recs', function (Blueprint $table) {
                $table->dropForeign(['feature_id']);
                $table->dropForeign(['source_plan_id']);
                $table->dropForeign(['target_plan_id']);
            });
        }

        if (Schema::hasTable('subscription_usage')) {
            Schema::table('subscription_usage', function (Blueprint $table) {
                $table->dropForeign(['subscription_id']);
                $table->dropForeign(['feature_plan_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('school_transactions')) {
            Schema::table('school_transactions', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['payment_method_id']);
            });
        }

        if (Schema::hasTable('school_subscriptions')) {
            Schema::table('school_subscriptions', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['plan_id']);
            });
        }

        if (Schema::hasTable('plan_features')) {
            Schema::table('plan_features', function (Blueprint $table) {
                $table->dropForeign(['feature_id']);
                $table->dropForeign(['plan_id']);
                $table->dropForeign(['country_id']);
            });
        }

        if (Schema::hasTable('plans')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }

        if (Schema::hasTable('features')) {
            Schema::table('features', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }
    }
};
