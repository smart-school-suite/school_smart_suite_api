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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('affiliate_applications');
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliate_payouts');
    }
};
