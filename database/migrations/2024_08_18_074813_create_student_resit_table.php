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
        Schema::create('student_resit', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('exam_status')->default('pending');
            $table->string('paid_status')->default('unpaid');
            $table->decimal('resit_fee', 8, 2)->default(3000.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_resit');
    }
};
