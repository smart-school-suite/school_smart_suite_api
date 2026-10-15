<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_types', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->unique()->after('name');
        });

        // Backfill badge types seeded before the key column existed so the seeder matches them.
        $keysByName = [
            'Admin Verification' => 'verfication.badge',
            'School Top Performer' => 'performance.badge',
            'Student Election Role Winner' => 'election.badge',
        ];

        foreach ($keysByName as $name => $key) {
            DB::table('badge_types')->where('name', $name)->whereNull('key')->limit(1)->update(['key' => $key]);
        }
    }

    public function down(): void
    {
        Schema::table('badge_types', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
