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

        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('max_number_students');
            $table->integer('max_number_parents');
            $table->integer('max_number_school_admins');
            $table->integer('max_number_teacher');
            $table->decimal('total_monthly_cost', 10, 2)->nullable();
            $table->decimal('total_yearly_cost', 10, 2)->nullable();
            $table->enum('billing_frequency', ['monthly', 'yearly']);
            $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active');
            $table->timestamp('subscription_start_date');
            $table->timestamp('subscription_end_date');
            $table->timestamp('subscription_renewal_date')->nullable();
            $table->boolean('auto_renewal')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('rate_cards', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('min_students');
            $table->integer('max_students');
            $table->integer('max_school_admins');
            $table->integer('max_teachers');
            $table->decimal('monthly_rate_per_student', 10, 2);
            $table->decimal('yearly_rate_per_student', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamp('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->enum('payment_status', ['completed', 'failed', 'pending']);
            $table->string('transaction_id')->nullable();
            $table->string('currency', 3)->default('XAF');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('rate_cards');
    }
};
