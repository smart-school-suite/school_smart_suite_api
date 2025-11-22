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
        Schema::create('student_resits', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('paid_status')->default('unpaid');
            $table->decimal('resit_fee', 8, 2)->default(3000.00);
            $table->unsignedInteger('attempt_number')->default(0);
            $table->unsignedInteger('iscarry_over')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_resits');
    }
};
