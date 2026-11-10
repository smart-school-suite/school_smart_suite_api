<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Event images get a display order; position 0 is the event's cover image.
     */
    public function up(): void
    {
        if (!Schema::hasTable('school_event_pics') || Schema::hasColumn('school_event_pics', 'position')) {
            return;
        }

        Schema::table('school_event_pics', function (Blueprint $table) {
            $table->unsignedSmallInteger('position')->default(0)->after('is_current');
        });

        // Keep the current upload order for images saved before this column existed.
        DB::table('school_event_pics')
            ->orderBy('event_id')
            ->orderBy('created_at')
            ->get(['id', 'event_id'])
            ->groupBy('event_id')
            ->each(function ($images) {
                $images->values()->each(
                    fn($image, $index) => DB::table('school_event_pics')
                        ->where('id', $image->id)
                        ->update(['position' => $index])
                );
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('school_event_pics') && Schema::hasColumn('school_event_pics', 'position')) {
            Schema::table('school_event_pics', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }
};
