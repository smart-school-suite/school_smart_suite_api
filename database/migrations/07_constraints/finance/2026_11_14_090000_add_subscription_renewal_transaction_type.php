<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renewals are recorded as their own transaction type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_transactions', function (Blueprint $table) {
            $table->enum('type', [
                'activation_code_purchase',
                'subscription_purchase',
                'subscription_upgrade',
                'subscription_renewal',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('school_transactions', function (Blueprint $table) {
            $table->enum('type', [
                'activation_code_purchase',
                'subscription_purchase',
                'subscription_upgrade',
            ])->change();
        });
    }
};
