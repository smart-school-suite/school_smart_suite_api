<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('key', 100);
            $table->string('name', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('limit_type', ['integer', 'decimal', 'boolean']);
            $table->json('default');
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('key', 100);
            $table->string('name', 150);
            $table->decimal('price', 15, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('max_plan')->default(false);
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->json('value');
            $table->enum('type', ['integer', 'decimal', 'boolean']);
            $table->json('default');
            $table->timestamps();
        });

        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled']);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('school_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->enum('type', ['activation_code_purchase', 'subscription_purchase', 'subscription_upgrade']);
            $table->decimal('amount', 15, 2);
            $table->string('payment_ref');
            $table->string('transaction_id');
            $table->enum('status', ['pending', 'failed', 'completed']);
            $table->timestamps();
        });

        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->json('limit');
            $table->json('current_usage');
            $table->enum('limit_type', ['integer', 'decimal', 'boolean']);
            $table->timestamps();
        });

        Schema::create('plan_recs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedSmallInteger('priority');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('plan_rec_conds', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('operator', 100);
            $table->json('value');
            $table->timestamps();
        });

        Schema::create('plan_rec_copies', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title', 150);
            $table->string('cta_text', 100);
            $table->text('description');
            $table->timestamps();
        });

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

        Schema::dropIfExists('plan_rec_copies');
        Schema::dropIfExists('plan_rec_conds');
        Schema::dropIfExists('plan_recs');
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('school_transactions');
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('features');
    }
};
