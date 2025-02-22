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
        Schema::create('resit_fee_transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'credit_card', 'debit_card', 'bank_transfer']);
            $table->string('resitfee_id');
            $table->foreign('resitfee_id')->references('id')->on('student_resit');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resit_fee_transactions');
    }
};
