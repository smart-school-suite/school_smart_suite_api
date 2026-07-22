<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_durations', function (Blueprint $table) {
            $table->uuid('type_id');
            $table->foreign('type_id')->references('id')->on('period_duration_types');
        });
    }

    public function down(): void
    {
        Schema::table('period_durations', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });
    }
};
