<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->enum('status', ['active', 'inactive', 'maintainance'])->default('active');
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
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('payment_method', function (Blueprint $table) {
            $table->string('country_id')->index();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('category_id')->index();
            $table->foreign('category_id')->references('id')->on('payment_method_categories');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_method')) {
            Schema::table('payment_method', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropForeign(['category_id']);
            });
        }

        Schema::dropIfExists('payment_method');
        Schema::dropIfExists('payment_method_categories');
    }
};
