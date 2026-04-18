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
        Schema::table('levels', function (Blueprint $table) {
            $table->string('level_type_id');
            $table->foreign('level_type_id')->references('id')->on('level_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('levels')) {
            Schema::table('levels', function (Blueprint $table) {
                $table->dropForeign(['level_type_id']);
            });
        }
    }
};
