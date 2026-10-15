<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_method', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('id');
            $table->unique(['key', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_method', function (Blueprint $table) {
            $table->dropUnique(['key', 'country_id']);
            $table->dropColumn('key');
        });
    }
};
