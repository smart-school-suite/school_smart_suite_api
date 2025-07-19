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
        Schema::create('registration_fees', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title')->default('Registration Fees');
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['paid', 'not paid', 'bad debt'])->default('not paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fees');
    }
};
