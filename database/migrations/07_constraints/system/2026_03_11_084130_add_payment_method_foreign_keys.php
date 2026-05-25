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
        Schema::table('payment_method', function (Blueprint $table) {
            $table->string('country_id')->index();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('category_id')->index();
            $table->foreign('category_id')->references('id')->on('payment_method_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_method', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['country_id', 'category_id']);
        });
    }
};
