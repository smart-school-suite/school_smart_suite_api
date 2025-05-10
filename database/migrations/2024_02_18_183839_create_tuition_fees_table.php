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
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->decimal('amount_left', 8, 2)->default(0.00);
            $table->decimal('tution_fee_total', 8, 2);
            $table->enum('status', ['completed', 'owing', 'bad debt'])->default('owing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_fees');
    }
};
