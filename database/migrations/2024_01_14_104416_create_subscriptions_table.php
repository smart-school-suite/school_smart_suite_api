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
        Schema::create('features', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('key', 100);
            $table->string('name', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('limit_type', ['integer','decimal','boolean']);
            $table->json('default');
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('key', 100);
            $table->string('name', 150);
            $table->decimal('price', 15, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
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
            $table->decimal('amount', 8, 2);
            $table->string('payment_ref');
            $table->string('transaction_id');
            $table->enum('status', ['pending', 'failed', 'completed']);
            $table->timestamps();
        });

        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->json('limit');
            $table->json('current_usage');
            $table->enum('limit_type', ['integer','decimal','boolean']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('school_transactions');
        Schema::dropIfExists('subscription_usage');
    }
};
