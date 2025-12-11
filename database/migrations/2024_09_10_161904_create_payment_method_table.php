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
        Schema::create('payment_method_category', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->enum('status', ['active','inactive','maintainance'])->default('active');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('payment_method', function (Blueprint $table) {
             $table->string('id')->primary();
             $table->string('name')->unique();
             $table->decimal('max_deposit', 12, 2);
             $table->decimal('max_withdraw', 12, 2);
             $table->string('operator_img')->nullable();
             $table->text('description')->nullable();
             $table->enum('status', ['active','inactive'])->default('active');
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method');
    }
};
