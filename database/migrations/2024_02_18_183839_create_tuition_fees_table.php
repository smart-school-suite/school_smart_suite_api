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
        Schema::create('tuition_fees', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->decimal('amount_left', 15, 2)->default(0.00);
            $table->decimal('tution_fee_total', 15, 2);
            $table->enum('status', ['completed', 'owing', 'bad debt'])->default('owing');
            $table->timestamps();
        });

        Schema::create('tuition_fee_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'credit_card', 'debit_card', 'bank_transfer']);
            $table->timestamps();
        });

        Schema::create('fee_waivers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['expired', 'active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_fees');
        Schema::dropIfExists('tuition_fee_transactions');
        Schema::dropIfExists('fee_waivers');
    }
};
