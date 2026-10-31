<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_versions', function (Blueprint $table) {
            $table->enum('scheduler_status', ['pending', 'generating', 'optimal', 'partial', 'failed', 'error'])
                ->default('pending')
                ->nullable()
                ->change();
        });

        DB::table('timetable_versions')->whereNull('scheduler_status')->update(['scheduler_status' => 'pending']);
    }

    public function down(): void
    {
        DB::table('timetable_versions')
            ->whereIn('scheduler_status', ['pending', 'generating'])
            ->update(['scheduler_status' => null]);

        Schema::table('timetable_versions', function (Blueprint $table) {
            $table->enum('scheduler_status', ['optimal', 'partial', 'failed', 'error'])
                ->default(null)
                ->nullable()
                ->change();
        });
    }
};
