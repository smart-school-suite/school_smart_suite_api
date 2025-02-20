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
        Schema::create('fee_payment_schedule', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->integer('num_installments');
            $table->decimal('amount', 8, 2);
            $table->date('due_date');
            $table->enum('type', ['one time', 'installmental'])->default('installmental');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payment_schedule');
    }
};
